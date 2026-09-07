<script setup lang="ts">
defineProps<{ title: string; description: string }>();
</script>

<template>
    <div
        class="rounded-lg border border-dashed border-slate-300 bg-white px-6 py-12 text-center"
    >
        <h2 class="text-lg font-semibold">{{ title }}</h2>
        <p class="mx-auto mt-2 max-w-lg text-sm text-slate-500">
            {{ description }}
        </p>
        <div class="mt-5"><slot /></div>
    </div>
</template>
