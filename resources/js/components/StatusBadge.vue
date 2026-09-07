<script setup lang="ts">
import type { IncidentStatus } from '@/types';

defineProps<{ status: IncidentStatus; label: string }>();

const classes: Record<IncidentStatus, string> = {
    open: 'bg-blue-100 text-blue-700',
    investigating: 'bg-amber-100 text-amber-800',
    resolved: 'bg-emerald-100 text-emerald-700',
};
</script>

<template>
    <span
        class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
        :class="classes[status]"
        >{{ label }}</span
    >
</template>
