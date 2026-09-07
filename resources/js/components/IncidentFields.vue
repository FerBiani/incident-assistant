<script setup lang="ts">
import type { Incident, IncidentSeverity, SelectOption } from '@/types';

withDefaults(
    defineProps<{
        incident?: Incident;
        projects: SelectOption<number>[];
        severities: SelectOption<IncidentSeverity>[];
        errors?: Record<string, string>;
        selectedProjectId?: number | null;
    }>(),
    { incident: undefined, errors: () => ({}), selectedProjectId: null },
);
</script>

<template>
    <div class="grid gap-5">
        <div>
            <label for="project_id" class="text-sm font-medium text-slate-700"
                >Projeto <span class="text-red-600">*</span></label
            >
            <select
                id="project_id"
                name="project_id"
                required
                :value="incident?.project.id ?? selectedProjectId ?? ''"
                class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100"
            >
                <option disabled value="">Selecione um projeto</option>
                <option
                    v-for="project in projects"
                    :key="project.value"
                    :value="project.value"
                >
                    {{ project.label }}
                </option>
            </select>
            <p v-if="errors.project_id" class="mt-1 text-sm text-red-600">
                {{ errors.project_id }}
            </p>
        </div>
        <div>
            <label for="title" class="text-sm font-medium text-slate-700"
                >Título <span class="text-red-600">*</span></label
            >
            <input
                id="title"
                name="title"
                type="text"
                maxlength="255"
                required
                :value="incident?.title"
                class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100"
            />
            <p v-if="errors.title" class="mt-1 text-sm text-red-600">
                {{ errors.title }}
            </p>
        </div>
        <div>
            <label for="severity" class="text-sm font-medium text-slate-700"
                >Severidade <span class="text-red-600">*</span></label
            >
            <select
                id="severity"
                name="severity"
                required
                :value="incident?.severity ?? ''"
                class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100"
            >
                <option disabled value="">Selecione a severidade</option>
                <option
                    v-for="severity in severities"
                    :key="severity.value"
                    :value="severity.value"
                >
                    {{ severity.label }}
                </option>
            </select>
            <p v-if="errors.severity" class="mt-1 text-sm text-red-600">
                {{ errors.severity }}
            </p>
        </div>
        <div>
            <label for="description" class="text-sm font-medium text-slate-700"
                >Descrição</label
            >
            <textarea
                id="description"
                name="description"
                rows="6"
                :value="incident?.description ?? ''"
                class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100"
            />
            <p v-if="errors.description" class="mt-1 text-sm text-red-600">
                {{ errors.description }}
            </p>
        </div>
        <div>
            <label for="logs" class="text-sm font-medium text-slate-700"
                >Logs ou stack trace</label
            >
            <textarea
                id="logs"
                name="logs"
                rows="10"
                :value="incident?.logs ?? ''"
                class="mt-1.5 w-full rounded-lg border border-slate-300 bg-slate-950 px-3 py-2 font-mono text-sm text-slate-100 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
            />
            <p v-if="errors.logs" class="mt-1 text-sm text-red-600">
                {{ errors.logs }}
            </p>
        </div>
    </div>
</template>
