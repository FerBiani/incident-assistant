<script setup lang="ts">
import { index, store } from '@/actions/App/Http/Controllers/ProjectController';
import ProjectFields from '@/components/ProjectFields.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Form, Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <AppLayout>
        <Head title="Novo projeto" />
        <div class="max-w-2xl">
            <h1 class="text-2xl font-semibold">Novo projeto</h1>
            <p class="mt-1 text-sm text-slate-500">
                Cadastre o contexto que organizará seus incidentes.
            </p>
            <Form
                :action="store()"
                class="mt-6 rounded-lg border border-slate-200 bg-white p-6"
                #default="{ errors, processing }"
            >
                <ProjectFields :errors="errors" />
                <div class="mt-6 flex gap-3">
                    <button
                        type="submit"
                        :disabled="processing"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                    >
                        {{
                            processing ? 'Salvando...' : 'Salvar projeto'
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
