<script setup lang="ts">
import {
    show,
    update,
} from '@/actions/App/Http/Controllers/IncidentController';
import IncidentFields from '@/components/IncidentFields.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Incident, IncidentSeverity, SelectOption } from '@/types';
import { Form, Head, Link } from '@inertiajs/vue3';

defineProps<{
    incident: Incident;
    projects: SelectOption<number>[];
    severities: SelectOption<IncidentSeverity>[];
}>();
</script>

<template>
    <AppLayout>
        <Head :title="`Editar ${incident.title}`" />
        <div class="max-w-3xl">
            <h1 class="text-2xl font-semibold">Editar incidente</h1>
            <Form
                :action="update(incident)"
                class="mt-6 rounded-lg border border-slate-200 bg-white p-6"
                #default="{ errors, processing }"
            >
                <IncidentFields
                    :incident="incident"
                    :projects="projects"
                    :severities="severities"
                    :errors="errors"
                />
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
                        :href="show(incident)"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700"
                        >Cancelar</Link
                    >
                </div>
            </Form>
        </div>
    </AppLayout>
</template>
