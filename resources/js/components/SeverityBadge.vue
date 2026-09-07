<script setup lang="ts">
import type { IncidentSeverity } from '@/types';

defineProps<{ severity: IncidentSeverity; label: string }>();

const classes: Record<IncidentSeverity, string> = {
    low: 'bg-slate-100 text-slate-700',
    medium: 'bg-blue-100 text-blue-700',
    high: 'bg-orange-100 text-orange-800',
    critical: 'bg-red-100 text-red-700',
};
</script>

<template>
    <span
        class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
        :class="classes[severity]"
        >{{ label }}</span
    >
</template>
