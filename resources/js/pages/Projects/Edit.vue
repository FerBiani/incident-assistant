<script setup lang="ts">
import { show, update } from '@/actions/App/Http/Controllers/ProjectController';
import ProjectFields from '@/components/ProjectFields.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Project } from '@/types';
import { Form, Head, Link } from '@inertiajs/vue3';

defineProps<{ project: Project }>();
</script>

<template>
    <AppLayout>
        <Head :title="`Editar ${project.name}`" />
        <div class="max-w-2xl">
            <h1 class="text-2xl font-semibold">Editar projeto</h1>
            <Form
                :action="update(project)"
                class="mt-6 rounded-lg border border-slate-200 bg-white p-6"
                #default="{ errors, processing }"
            >
                <ProjectFields :project="project" :errors="errors" />
                <div class="mt-6 flex gap-3">
                    <button
                        type="submit"
                        :disabled="processing"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                    >
                        {{
                            processing ? 'Salvando...' : 'Salvar alterações'
                        }}</button
                    ><Link
                        :href="show(project)"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700"
                        >Cancelar</Link
                    >
                </div>
            </Form>
        </div>
    </AppLayout>
</template>
