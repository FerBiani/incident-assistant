<script setup lang="ts">
import IncidentConversationController from '@/actions/App/Http/Controllers/IncidentConversationController';
import type {
    ApprovalDecision,
    Incident,
    IncidentApproval,
    IncidentConversation,
    IncidentStreamEvent,
    ToolApprovalRequest,
} from '@/types';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    incident: Incident;
    conversation: IncidentConversation | null;
}>();

const draft = ref('');
const generating = ref(false);
const streamedText = ref('');
const optimisticMessage = ref('');
const streamError = ref('');
const liveApprovals = ref<ToolApprovalRequest[]>([]);
const decisions = ref<Record<string, ApprovalDecision>>({});
const resolvedApprovalIds = ref<Set<string>>(new Set());

const storedPendingApprovals = computed<IncidentApproval[]>(() =>
    (props.conversation?.messages ?? [])
        .flatMap((message) => message.approvals)
        .filter(
            (approval) =>
                approval.status === 'pending' &&
                !resolvedApprovalIds.value.has(approval.id),
        ),
);

const pendingApprovals = computed(() => {
    const approvals = new Map<
        string,
        { id: string; description: string; content?: string }
    >();

    for (const approval of storedPendingApprovals.value) {
        approvals.set(approval.id, {
            id: approval.id,
            description: approval.description,
            content: approval.arguments.content,
        });
    }

    for (const approval of liveApprovals.value) {
        if (resolvedApprovalIds.value.has(approval.id)) {
            continue;
        }

        approvals.set(approval.id, {
            id: approval.id,
            description: approval.reason ?? 'Ação solicitada pelo assistente',
            content:
                typeof approval.arguments.content === 'string'
                    ? approval.arguments.content
                    : undefined,
        });
    }

    return [...approvals.values()];
});

const allDecisionsSelected = computed(
    () =>
        pendingApprovals.value.length > 0 &&
        pendingApprovals.value.every(
            (approval) => decisions.value[approval.id],
        ),
);

function csrfToken(): string {
    return (
        document
            .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? ''
    );
}

async function sendMessage(): Promise<void> {
    const message = draft.value.trim();

    if (!message || generating.value || pendingApprovals.value.length > 0) {
        return;
    }

    draft.value = '';
    optimisticMessage.value = message;
    await stream({ message });
}

async function sendDecisions(): Promise<void> {
    if (!allDecisionsSelected.value || generating.value) {
        return;
    }

    const payload: Record<string, { action: ApprovalDecision }> = {};
    for (const approval of pendingApprovals.value) {
        payload[approval.id] = { action: decisions.value[approval.id] };
    }

    await stream({ decisions: payload });
}

async function stream(payload: object): Promise<void> {
    generating.value = true;
    streamedText.value = '';
    streamError.value = '';

    try {
        const response = await fetch(
            IncidentConversationController(props.incident).url,
            {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'text/event-stream, application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(payload),
            },
        );

        if (!response.ok || !response.body) {
            const data = (await response.json().catch(() => null)) as {
                message?: string;
                errors?: Record<string, string[]>;
            } | null;
            throw new Error(
                data?.errors
                    ? Object.values(data.errors).flat()[0]
                    : (data?.message ??
                          'Não foi possível continuar a conversa.'),
            );
        }

        await consumeSse(response.body);
        await reloadConversation(true);
    } catch (error) {
        streamError.value =
            error instanceof Error
                ? error.message
                : 'A resposta foi interrompida inesperadamente.';
        await reloadConversation(false);
    } finally {
        generating.value = false;
    }
}

async function consumeSse(body: ReadableStream<Uint8Array>): Promise<void> {
    const reader = body.getReader();
    const decoder = new TextDecoder();
    let buffer = '';

    while (true) {
        const { done, value } = await reader.read();
        buffer += decoder
            .decode(value, { stream: !done })
            .replace(/\r\n/g, '\n');

        let boundary = buffer.indexOf('\n\n');
        while (boundary !== -1) {
            const frame = buffer.slice(0, boundary);
            buffer = buffer.slice(boundary + 2);
            handleFrame(frame);
            boundary = buffer.indexOf('\n\n');
        }

        if (done) {
            if (buffer.trim()) {
                handleFrame(buffer);
            }
            break;
        }
    }
}

function handleFrame(frame: string): void {
    const data = frame
        .split('\n')
        .filter((line) => line.startsWith('data:'))
        .map((line) => line.slice(5).trimStart())
        .join('\n');

    if (!data || data === '[DONE]') {
        return;
    }

    const event = JSON.parse(data) as IncidentStreamEvent;

    if (event.type === 'text_delta') {
        streamedText.value += event.delta ?? '';
    } else if (event.type === 'tool_approval_request' && event.approvals) {
        liveApprovals.value = event.approvals;
    } else if (event.type === 'tool_result' && event.tool_id) {
        resolvedApprovalIds.value = new Set([
            ...resolvedApprovalIds.value,
            event.tool_id,
        ]);
    } else if (event.type === 'error') {
        throw new Error(
            event.message ?? event.error ?? 'A geração foi interrompida.',
        );
    }
}

function reloadConversation(completed: boolean): Promise<void> {
    return new Promise((resolve) => {
        router.reload({
            only: ['incident', 'conversation'],
            onFinish: () => {
                if (completed) {
                    streamedText.value = '';
                    optimisticMessage.value = '';
                    liveApprovals.value = [];
                    decisions.value = {};
                    resolvedApprovalIds.value = new Set();
                }
                resolve();
            },
        });
    });
}

function formatDate(value: string): string {
    return new Intl.DateTimeFormat('pt-BR', {
        dateStyle: 'short',
        timeStyle: 'short',
    }).format(new Date(value));
}
</script>

<template>
    <section class="rounded-lg border border-slate-200 bg-white p-5">
        <div>
            <h2 class="text-lg font-semibold">Assistente de investigação</h2>
            <p class="mt-1 text-sm text-slate-500">
                O assistente considera o incidente, sua triagem e suas notas.
                Incidentes relacionados só são consultados pela ferramenta de
                busca.
            </p>
        </div>

        <ol
            class="mt-5 max-h-[36rem] space-y-4 overflow-y-auto pr-1"
            aria-live="polite"
        >
            <li
                v-for="message in conversation?.messages ?? []"
                :key="message.id"
                class="rounded-lg border p-4"
                :class="
                    message.role === 'user'
                        ? 'ml-6 border-slate-200 bg-slate-50'
                        : 'mr-6 border-blue-100 bg-blue-50/50'
                "
            >
                <div class="flex items-center justify-between gap-3 text-xs">
                    <strong class="text-slate-700">{{
                        message.role === 'user' ? 'Usuário' : 'Assistente de IA'
                    }}</strong>
                    <time
                        :datetime="message.created_at"
                        class="text-slate-500"
                        >{{ formatDate(message.created_at) }}</time
                    >
                </div>
                <p
                    v-if="message.content"
                    class="mt-2 text-sm break-words whitespace-pre-wrap text-slate-800"
                >
                    {{ message.content }}
                </p>
                <div
                    v-for="approval in message.approvals"
                    :key="approval.id"
                    class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3"
                >
                    <div
                        class="flex flex-wrap items-center justify-between gap-2"
                    >
                        <strong class="text-sm text-amber-900"
                            >Ação: {{ approval.description }}</strong
                        >
                        <span
                            class="rounded-full bg-white px-2 py-1 text-xs font-medium text-slate-700"
                        >
                            {{
                                approval.status === 'pending'
                                    ? 'Pendente'
                                    : approval.status === 'approved'
                                      ? 'Aprovada'
                                      : approval.status === 'rejected'
                                        ? 'Rejeitada'
                                        : 'Falhou'
                            }}
                        </span>
                    </div>
                    <p
                        v-if="approval.arguments.content"
                        class="mt-2 text-sm whitespace-pre-wrap text-slate-700"
                    >
                        {{ approval.arguments.content }}
                    </p>
                </div>
            </li>

            <li
                v-if="optimisticMessage"
                class="ml-6 rounded-lg border border-slate-200 bg-slate-50 p-4"
            >
                <strong class="text-xs text-slate-700">Usuário</strong>
                <p class="mt-2 text-sm whitespace-pre-wrap text-slate-800">
                    {{ optimisticMessage }}
                </p>
            </li>
            <li
                v-if="streamedText || generating"
                class="mr-6 rounded-lg border border-blue-100 bg-blue-50/50 p-4"
            >
                <strong class="text-xs text-slate-700">Assistente de IA</strong>
                <p
                    v-if="streamedText"
                    class="mt-2 text-sm break-words whitespace-pre-wrap text-slate-800"
                >
                    {{ streamedText }}
                </p>
                <p v-else class="mt-2 animate-pulse text-sm text-slate-500">
                    Gerando resposta...
                </p>
            </li>
        </ol>

        <p
            v-if="streamError"
            class="mt-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"
            role="alert"
        >
            {{ streamError }} Os trechos já recebidos foram mantidos.
        </p>

        <div
            v-if="pendingApprovals.length"
            class="mt-5 space-y-3 rounded-lg border border-amber-300 bg-amber-50 p-4"
        >
            <div>
                <h3 class="font-semibold text-amber-950">
                    Aprovação humana necessária
                </h3>
                <p class="text-sm text-amber-800">
                    Revise todas as ações. Nada será executado antes do envio
                    das decisões.
                </p>
            </div>
            <fieldset
                v-for="approval in pendingApprovals"
                :key="approval.id"
                class="rounded-lg border border-amber-200 bg-white p-3"
            >
                <legend class="px-1 text-sm font-medium text-slate-800">
                    {{ approval.description }}
                </legend>
                <p
                    v-if="approval.content"
                    class="mt-1 text-sm whitespace-pre-wrap text-slate-700"
                >
                    Conteúdo proposto: {{ approval.content }}
                </p>
                <div class="mt-3 flex gap-4 text-sm">
                    <label class="flex items-center gap-2"
                        ><input
                            v-model="decisions[approval.id]"
                            type="radio"
                            :name="`decision-${approval.id}`"
                            value="approve"
                            :disabled="generating"
                        />
                        Aprovar</label
                    >
                    <label class="flex items-center gap-2"
                        ><input
                            v-model="decisions[approval.id]"
                            type="radio"
                            :name="`decision-${approval.id}`"
                            value="reject"
                            :disabled="generating"
                        />
                        Rejeitar</label
                    >
                </div>
            </fieldset>
            <button
                type="button"
                :disabled="!allDecisionsSelected || generating"
                class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-50"
                @click="sendDecisions"
            >
                {{ generating ? 'Processando...' : 'Enviar decisões' }}
            </button>
        </div>

        <form class="mt-5" @submit.prevent="sendMessage">
            <label
                for="assistant-message"
                class="text-sm font-medium text-slate-700"
                >Mensagem para o assistente</label
            >
            <textarea
                id="assistant-message"
                v-model="draft"
                rows="4"
                maxlength="10000"
                class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                :disabled="generating || pendingApprovals.length > 0"
                placeholder="Descreva o que deseja investigar..."
            />
            <div class="mt-3 flex items-center justify-between gap-3">
                <p
                    v-if="pendingApprovals.length"
                    class="text-sm text-amber-700"
                >
                    Decida as ações pendentes para continuar.
                </p>
                <span v-else />
                <button
                    type="submit"
                    :disabled="
                        generating ||
                        pendingApprovals.length > 0 ||
                        !draft.trim()
                    "
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {{ generating ? 'Gerando...' : 'Enviar mensagem' }}
                </button>
            </div>
        </form>
    </section>
</template>
