<script setup lang="ts">
import type { Project } from '@/types';

withDefaults(
    defineProps<{ project?: Project; errors?: Record<string, string> }>(),
    {
        project: undefined,
        errors: () => ({}),
    },
);
</script>

<template>
    <div class="grid gap-5">
        <div>
            <label for="name" class="text-sm font-medium text-slate-700"
                >Nome <span class="text-red-600">*</span></label
            >
            <input
                id="name"
                name="name"
                type="text"
                maxlength="255"
                required
                :value="project?.name"
                class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100"
            />
            <p v-if="errors.name" class="mt-1 text-sm text-red-600">
                {{ errors.name }}
            </p>
        </div>
        <div>
            <label for="description" class="text-sm font-medium text-slate-700"
                >Descrição</label
            >
            <textarea
                id="description"
                name="description"
                rows="5"
                :value="project?.description ?? ''"
                class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-100"
            />
            <p v-if="errors.description" class="mt-1 text-sm text-red-600">
                {{ errors.description }}
            </p>
        </div>
    </div>
</template>
