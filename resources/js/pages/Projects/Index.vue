<script setup lang="ts">
import { create, show } from '@/actions/App/Http/Controllers/ProjectController';
import EmptyState from '@/components/EmptyState.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Paginated, Project } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{ projects: Paginated<Project> }>();
</script>

<template>
    <AppLayout>
        <Head title="Projetos" />
        <div class="flex flex-col gap-6">
            <header
                class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
            >
                <div>
                    <h1 class="text-2xl font-semibold">Projetos</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Organize os incidentes por aplicação, serviço ou
                        sistema.
                    </p>
                </div>
                <Link
                    :href="create()"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white hover:bg-blue-700"
                    >Novo projeto</Link
                >
            </header>

            <EmptyState
                v-if="projects.data.length === 0"
                title="Nenhum projeto cadastrado"
                description="Cadastre o primeiro projeto para começar a registrar incidentes."
            >
                <Link
                    :href="create()"
                    class="inline-flex rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white"
                    >Cadastrar projeto</Link
                >
            </EmptyState>

            <div
                v-else
                class="overflow-hidden rounded-lg border border-slate-200 bg-white"
            >
                <div
                    v-for="project in projects.data"
                    :key="project.id"
                    class="flex flex-col gap-2 border-b border-slate-200 p-5 last:border-b-0 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <Link
                            :href="show(project)"
                            class="font-semibold text-slate-900 hover:text-blue-700"
                            >{{ project.name }}</Link
                        >
                        <p
                            v-if="project.description"
                            class="mt-1 text-sm text-slate-500"
                        >
                            {{ project.description }}
                        </p>
                    </div>
                    <span class="text-sm text-slate-500"
                        >{{ project.incidents_count ?? 0 }} incidente(s)</span
                    >
                </div>
            </div>
            <PaginationLinks :links="projects.meta.links" />
        </div>
    </AppLayout>
</template>
