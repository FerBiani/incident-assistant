<script setup lang="ts">
import {
    destroy,
    edit,
    index,
} from '@/actions/App/Http/Controllers/IncidentController';
import { store as startInvestigation } from '@/actions/App/Http/Controllers/IncidentInvestigationController';
import { store as addNote } from '@/actions/App/Http/Controllers/IncidentNoteController';
import {
    destroy as reopen,
    store as resolve,
} from '@/actions/App/Http/Controllers/IncidentResolutionController';
import EmptyState from '@/components/EmptyState.vue';
import SeverityBadge from '@/components/SeverityBadge.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Incident } from '@/types';
import { Form, Head, Link, router } from '@inertiajs/vue3';

const props = defineProps<{ incident: Incident }>();

function formatDate(value: string): string {
    return new Intl.DateTimeFormat('pt-BR', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

function deleteIncident(): void {
    if (window.confirm(`Excluir o incidente "${props.incident.title}"?`)) {
        router.delete(destroy(props.incident).url);
    }
}
</script>

<template>
    <AppLayout>
        <Head :title="incident.title" />
        <div class="flex flex-col gap-8">
            <header
                class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between"
            >
                <div class="min-w-0">
                    <Link
                        :href="index()"
                        class="text-sm text-blue-700 hover:underline"
                        >← Incidentes</Link
                    >
                    <h1 class="mt-2 text-2xl font-semibold break-words">
                        {{ incident.title }}
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Projeto: {{ incident.project.name }}
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <SeverityBadge
                            :severity="incident.severity"
                            :label="incident.severity_label"
                        />
                        <StatusBadge
                            :status="incident.status"
                            :label="incident.status_label"
                        />
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button
                        v-if="incident.status === 'open'"
                        type="button"
                        class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-medium text-white"
                        @click="router.post(startInvestigation(incident).url)"
                    >
                        Iniciar investigação
                    </button>
                    <button
                        v-if="
                            incident.status === 'open' ||
                            incident.status === 'investigating'
                        "
                        type="button"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white"
                        @click="router.post(resolve(incident).url)"
                    >
                        Resolver
                    </button>
                    <button
                        v-if="incident.status === 'resolved'"
                        type="button"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white"
                        @click="router.delete(reopen(incident).url)"
                    >
                        Reabrir
                    </button>
                    <Link
                        :href="edit(incident)"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700"
                    >
                        Editar
                    </Link>
                    <button
                        type="button"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white"
                        @click="deleteIncident"
                    >
                        Excluir
                    </button>
                </div>
            </header>

            <section class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-lg border border-slate-200 bg-white p-5">
                    <h2 class="font-semibold">Descrição</h2>
                    <p
                        v-if="incident.description"
                        class="mt-3 text-sm break-words whitespace-pre-wrap text-slate-700"
                    >
                        {{ incident.description }}
                    </p>
                    <p v-else class="mt-3 text-sm text-slate-500">
                        Nenhuma descrição informada.
                    </p>
                </div>

                <div class="rounded-lg border border-slate-200 bg-white p-5">
                    <h2 class="font-semibold">Datas</h2>
                    <dl class="mt-3 space-y-2 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">Criado em</dt>
                            <dd>{{ formatDate(incident.created_at) }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">Atualizado em</dt>
                            <dd>{{ formatDate(incident.updated_at) }}</dd>
                        </div>
                    </dl>
                </div>
            </section>

            <section>
                <h2 class="text-lg font-semibold">Logs e stack trace</h2>
                <pre
                    v-if="incident.logs"
                    class="mt-3 overflow-x-auto rounded-lg bg-slate-950 p-5 font-mono text-sm whitespace-pre text-slate-100"
                    >{{ incident.logs }}</pre>
                <p
                    v-else
                    class="mt-3 rounded-lg border border-slate-200 bg-white p-5 text-sm text-slate-500"
                >
                    Nenhum log informado.
                </p>
            </section>

            <section class="flex flex-col gap-5">
                <div>
                    <h2 class="text-lg font-semibold">Notas de investigação</h2>
                    <p class="text-sm text-slate-500">
                        Histórico manual em ordem cronológica.
                    </p>
                </div>

                <Form
                    :action="addNote(incident)"
                    reset-on-success
                    class="rounded-lg border border-slate-200 bg-white p-5"
                    #default="{ errors, processing }"
                >
                    <label
                        for="content"
                        class="text-sm font-medium text-slate-700"
                        >Nova nota <span class="text-red-600">*</span></label
                    >
                    <textarea
                        id="content"
                        name="content"
                        rows="4"
                        required
                        class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                    />
                    <p v-if="errors.content" class="mt-1 text-sm text-red-600">
                        {{ errors.content }}
                    </p>
                    <button
                        type="submit"
                        :disabled="processing"
                        class="mt-3 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                    >
                        {{ processing ? 'Adicionando...' : 'Adicionar nota' }}
                    </button>
                </Form>

                <EmptyState
                    v-if="!incident.notes?.length"
                    title="Nenhuma nota registrada"
                    description="Adicione observações manuais conforme a investigação evoluir."
                />
                <ol v-else class="space-y-4">
                    <li
                        v-for="note in incident.notes"
                        :key="note.id"
                        class="rounded-lg border border-slate-200 bg-white p-5"
                    >
                        <time
                            :datetime="note.created_at"
                            class="text-xs font-medium text-slate-500"
                            >{{ formatDate(note.created_at) }}</time
                        >
                        <p
                            class="mt-2 text-sm break-words whitespace-pre-wrap text-slate-700"
                        >
                            {{ note.content }}
                        </p>
                    </li>
                </ol>
            </section>
        </div>
    </AppLayout>
</template>
