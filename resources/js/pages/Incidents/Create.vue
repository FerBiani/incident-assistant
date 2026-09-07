<script setup lang="ts">
import {
    index,
    store,
} from '@/actions/App/Http/Controllers/IncidentController';
import { create as createProject } from '@/actions/App/Http/Controllers/ProjectController';
import EmptyState from '@/components/EmptyState.vue';
import IncidentFields from '@/components/IncidentFields.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { IncidentSeverity, SelectOption } from '@/types';
import { Form, Head, Link, usePage } from '@inertiajs/vue3';

defineProps<{
    projects: SelectOption<number>[];
    severities: SelectOption<IncidentSeverity>[];
    canCreate: boolean;
}>();
const page = usePage();
const selectedProjectId =
    Number(
        new URLSearchParams(page.url.split('?')[1] ?? '').get('project_id'),
    ) || null;
</script>

<template>
    <AppLayout>
        <Head title="Novo incidente" />
        <div class="max-w-3xl">
            <h1 class="text-2xl font-semibold">Novo incidente</h1>
            <p class="mt-1 text-sm text-slate-500">
                Registre as informações disponíveis sobre o problema.
            </p>
            <EmptyState
                v-if="!canCreate"
                class="mt-6"
                title="Cadastre um projeto primeiro"
                description="Todo incidente precisa pertencer a um projeto existente."
                ><Link
                    :href="createProject()"
                    class="inline-flex rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white"
                    >Cadastrar projeto</Link
                ></EmptyState
            >
            <Form
                v-else
                :action="store()"
                class="mt-6 rounded-lg border border-slate-200 bg-white p-6"
                #default="{ errors, processing }"
            >
                <IncidentFields
                    :projects="projects"
                    :severities="severities"
                    :errors="errors"
                    :selected-project-id="selectedProjectId"
                />
                <div class="mt-6 flex gap-3">
                    <button
                        type="submit"
                        :disabled="processing"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                    >
                        {{
                            processing ? 'Salvando...' : 'Salvar incidente'
                        }}</button
                    ><Link
                        :href="index()"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700"
                        >Cancelar</Link
                    >
                </div>
            </Form>
        </div>
    </AppLayout>
</template>
