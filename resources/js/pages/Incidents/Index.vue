<script setup lang="ts">
import {
    create,
    index,
    show,
} from '@/actions/App/Http/Controllers/IncidentController';
import EmptyState from '@/components/EmptyState.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import SeverityBadge from '@/components/SeverityBadge.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type {
    IncidentSeverity,
    IncidentStatus,
    IncidentSummary,
    Paginated,
    SelectOption,
} from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps<{
    incidents: Paginated<IncidentSummary>;
    projects: SelectOption<number>[];
    severities: SelectOption<IncidentSeverity>[];
    statuses: SelectOption<IncidentStatus>[];
    filters: {
        project_id: number | null;
        severity: string | null;
        status: string | null;
    };
}>();

const filters = reactive({
    project_id: props.filters.project_id?.toString() ?? '',
    severity: props.filters.severity ?? '',
    status: props.filters.status ?? '',
});

const hasFilters = (): boolean => Object.values(filters).some(Boolean);

function applyFilters(): void {
    router.get(index().url, filters, { preserveState: true, replace: true });
}

function clearFilters(): void {
    filters.project_id = '';
    filters.severity = '';
    filters.status = '';
    router.get(index().url, {}, { preserveState: true, replace: true });
}
</script>

<template>
    <AppLayout>
        <Head title="Incidentes" />
        <div class="flex flex-col gap-6">
            <header
                class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
            >
                <div>
                    <h1 class="text-2xl font-semibold">Incidentes</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Acompanhe problemas técnicos de todos os projetos.
                    </p>
                </div>
                <Link
                    :href="create()"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white"
                    >Novo incidente</Link
                >
            </header>

            <form
                class="grid gap-4 rounded-lg border border-slate-200 bg-white p-4 sm:grid-cols-4 sm:items-end"
                @submit.prevent="applyFilters"
            >
                <div>
                    <label
                        for="project_id"
                        class="text-sm font-medium text-slate-700"
                        >Projeto</label
                    ><select
                        id="project_id"
                        v-model="filters.project_id"
                        class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                    >
                        <option value="">Todos</option>
                        <option
                            v-for="project in projects"
                            :key="project.value"
                            :value="project.value"
                        >
                            {{ project.label }}
                        </option>
                    </select>
                </div>
                <div>
                    <label
                        for="severity"
                        class="text-sm font-medium text-slate-700"
                        >Severidade</label
                    ><select
                        id="severity"
                        v-model="filters.severity"
                        class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                    >
                        <option value="">Todas</option>
                        <option
                            v-for="severity in severities"
                            :key="severity.value"
                            :value="severity.value"
                        >
                            {{ severity.label }}
                        </option>
                    </select>
                </div>
                <div>
                    <label
                        for="status"
                        class="text-sm font-medium text-slate-700"
                        >Status</label
                    ><select
                        id="status"
                        v-model="filters.status"
                        class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                    >
                        <option value="">Todos</option>
                        <option
                            v-for="status in statuses"
                            :key="status.value"
                            :value="status.value"
                        >
                            {{ status.label }}
                        </option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button
                        type="submit"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white"
                    >
                        Filtrar</button
                    ><button
                        v-if="hasFilters()"
                        type="button"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700"
                        @click="clearFilters"
                    >
                        Limpar
                    </button>
                </div>
            </form>

            <EmptyState
                v-if="incidents.data.length === 0"
                :title="
                    hasFilters()
                        ? 'Nenhum incidente encontrado'
                        : 'Nenhum incidente cadastrado'
                "
                :description="
                    hasFilters()
                        ? 'Ajuste ou limpe os filtros para ver outros incidentes.'
                        : 'Registre o primeiro incidente para iniciar o acompanhamento.'
                "
            >
                <button
                    v-if="hasFilters()"
                    type="button"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700"
                    @click="clearFilters"
                >
                    Limpar filtros
                </button>
                <Link
                    v-else
                    :href="create()"
                    class="inline-flex rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white"
                    >Cadastrar incidente</Link
                >
            </EmptyState>

            <div
                v-else
                class="overflow-x-auto rounded-lg border border-slate-200 bg-white"
            >
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">Incidente</th>
                            <th class="px-4 py-3 font-medium">Projeto</th>
                            <th class="px-4 py-3 font-medium">Severidade</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr
                            v-for="incident in incidents.data"
                            :key="incident.id"
                        >
                            <td class="px-4 py-4">
                                <Link
                                    :href="show(incident)"
                                    class="font-medium text-slate-900 hover:text-blue-700"
                                    >{{ incident.title }}</Link
                                >
                            </td>
                            <td class="px-4 py-4 text-slate-600">
                                {{ incident.project.name }}
                            </td>
                            <td class="px-4 py-4">
                                <SeverityBadge
                                    :severity="incident.severity"
                                    :label="incident.severity_label"
                                />
                            </td>
                            <td class="px-4 py-4">
                                <StatusBadge
                                    :status="incident.status"
                                    :label="incident.status_label"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <PaginationLinks :links="incidents.meta.links" />
        </div>
    </AppLayout>
</template>
