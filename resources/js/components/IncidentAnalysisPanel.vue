<script setup lang="ts">
import IncidentAnalysisController from '@/actions/App/Http/Controllers/IncidentAnalysisController';
import SeverityBadge from '@/components/SeverityBadge.vue';
import type { Incident } from '@/types';
import { router, usePoll } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps<{ incident: Incident }>();

const { start, stop } = usePoll(
    2_000,
    { only: ['incident'] },
    { autoStart: false },
);

watch(
    () => props.incident.analysis.status,
    (status) => {
        if (status === 'pending') {
            start();
        } else {
            stop();
        }
    },
    { immediate: true },
);

function requestAnalysis(): void {
    router.post(
        IncidentAnalysisController(props.incident).url,
        {},
        { preserveScroll: true },
    );
}

function formatDate(value: string): string {
    return new Intl.DateTimeFormat('pt-BR', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}
</script>

<template>
    <section class="rounded-lg border border-blue-200 bg-blue-50/40 p-5">
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
        >
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-lg font-semibold text-slate-900">
                        Triagem do incidente
                    </h2>
                    <span
                        class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-800"
                    >
                        Conteúdo gerado por IA
                    </span>
                </div>
                <p class="mt-1 text-sm text-slate-600">
                    Hipóteses e recomendações para apoiar a investigação humana.
                </p>
            </div>
            <button
                type="button"
                :disabled="incident.analysis.status === 'pending'"
                class="shrink-0 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-50"
                @click="requestAnalysis"
            >
                {{
                    incident.analysis.status === 'pending'
                        ? 'Analisando...'
                        : incident.analysis.analyzed_at
                          ? 'Analisar novamente'
                          : 'Analisar incidente'
                }}
            </button>
        </div>

        <div
            v-if="incident.analysis.status === 'pending'"
            class="mt-4 rounded-lg border border-blue-200 bg-white px-4 py-3 text-sm text-blue-800"
            role="status"
        >
            A análise está sendo atualizada em segundo plano. O resultado
            anterior permanece disponível abaixo.
        </div>
        <div
            v-if="incident.analysis.status === 'failed'"
            class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
            role="alert"
        >
            A última tentativa falhou. Você pode tentar novamente; uma análise
            anterior não foi apagada.
        </div>

        <div v-if="incident.analysis.analyzed_at" class="mt-5 space-y-5">
            <div>
                <p
                    class="text-xs font-semibold tracking-wide text-slate-500 uppercase"
                >
                    Resumo
                </p>
                <p class="mt-1 text-sm whitespace-pre-wrap text-slate-800">
                    {{ incident.analysis.summary }}
                </p>
            </div>
            <div v-if="incident.analysis.suggested_severity">
                <p
                    class="mb-2 text-xs font-semibold tracking-wide text-slate-500 uppercase"
                >
                    Severidade sugerida
                </p>
                <SeverityBadge
                    :severity="incident.analysis.suggested_severity"
                    :label="
                        incident.analysis.suggested_severity_label ??
                        incident.analysis.suggested_severity
                    "
                />
            </div>
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800">
                        Causas prováveis
                    </h3>
                    <ul
                        v-if="incident.analysis.probable_causes.length"
                        class="mt-2 list-disc space-y-1 pl-5 text-sm text-slate-700"
                    >
                        <li
                            v-for="cause in incident.analysis.probable_causes"
                            :key="cause"
                        >
                            {{ cause }}
                        </li>
                    </ul>
                    <p v-else class="mt-2 text-sm text-slate-500">
                        Nenhuma causa sugerida.
                    </p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-slate-800">
                        Ações recomendadas
                    </h3>
                    <ul
                        v-if="incident.analysis.recommended_actions.length"
                        class="mt-2 list-disc space-y-1 pl-5 text-sm text-slate-700"
                    >
                        <li
                            v-for="action in incident.analysis
                                .recommended_actions"
                            :key="action"
                        >
                            {{ action }}
                        </li>
                    </ul>
                    <p v-else class="mt-2 text-sm text-slate-500">
                        Nenhuma ação sugerida.
                    </p>
                </div>
            </div>
            <p class="text-xs text-slate-500">
                Última análise: {{ formatDate(incident.analysis.analyzed_at) }}
            </p>
        </div>
        <p
            v-else-if="incident.analysis.status !== 'pending'"
            class="mt-5 text-sm text-slate-600"
        >
            Este incidente ainda não possui uma análise de IA.
        </p>
    </section>
</template>
