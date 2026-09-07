<script setup lang="ts">
import {
    create as createIncident,
    show as showIncident,
} from '@/actions/App/Http/Controllers/IncidentController';
import {
    destroy,
    edit,
    index,
} from '@/actions/App/Http/Controllers/ProjectController';
import EmptyState from '@/components/EmptyState.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import SeverityBadge from '@/components/SeverityBadge.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { IncidentSummary, Paginated, Project } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps<{
    project: Project;
    incidents: Paginated<IncidentSummary>;
}>();

function deleteProject(): void {
    if (window.confirm(`Excluir o projeto "${props.project.name}"?`)) {
        router.delete(destroy(props.project).url);
    }
}
</script>

<template>
    <AppLayout>
        <Head :title="project.name" />
        <div class="flex flex-col gap-8">
            <header
                class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
            >
                <div>
                    <Link
                        :href="index()"
                        class="text-sm text-blue-700 hover:underline"
                        >← Projetos</Link
                    >
                    <h1 class="mt-2 text-2xl font-semibold">
                        {{ project.name }}
                    </h1>
                    <p
                        v-if="project.description"
                        class="mt-2 max-w-3xl text-slate-600"
                    >
                        {{ project.description }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link
                        :href="edit(project)"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700"
                        >Editar</Link
                    >
                    <button
                        type="button"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white"
                        @click="deleteProject"
                    >
                        Excluir
                    </button>
                </div>
            </header>

            <section class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Incidentes</h2>
                        <p class="text-sm text-slate-500">
                            {{ project.incidents_count ?? 0 }} registro(s) neste
                            projeto.
                        </p>
                    </div>
                    <Link
                        :href="
                            createIncident({
                                query: { project_id: project.id },
                            })
                        "
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white"
                        >Novo incidente</Link
                    >
                </div>
                <EmptyState
                    v-if="incidents.data.length === 0"
                    title="Nenhum incidente neste projeto"
                    description="Registre um incidente para iniciar o acompanhamento."
                >
                    <Link
                        :href="
                            createIncident({
                                query: { project_id: project.id },
                            })
                        "
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
                                <th class="px-4 py-3 font-medium">
                                    Severidade
                                </th>
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
                                        :href="showIncident(incident)"
                                        class="font-medium text-slate-900 hover:text-blue-700"
                                        >{{ incident.title }}</Link
                                    >
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
            </section>
        </div>
    </AppLayout>
</template>
