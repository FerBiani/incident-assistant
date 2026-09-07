<script setup lang="ts">
import type { PaginationLink } from '@/types';
import { Link } from '@inertiajs/vue3';

defineProps<{ links: PaginationLink[] }>();

function paginationLabel(label: string): string {
    if (label.includes('Previous')) {
        return 'Anterior';
    }

    if (label.includes('Next')) {
        return 'Próxima';
    }

    return label;
}
</script>

<template>
    <nav
        v-if="links.length > 3"
        aria-label="Paginação"
        class="flex flex-wrap gap-2"
    >
        <template v-for="link in links" :key="link.label">
            <span
                v-if="!link.url"
                class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-400"
            >
                {{ paginationLabel(link.label) }}
            </span>
            <Link
                v-else
                :href="link.url"
                preserve-scroll
                class="rounded-lg border px-3 py-2 text-sm"
                :class="
                    link.active
                        ? 'border-blue-600 bg-blue-600 text-white'
                        : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'
                "
            >
                {{ paginationLabel(link.label) }}
            </Link>
        </template>
    </nav>
</template>
