<script setup lang="ts">
import { index as incidentIndex } from '@/actions/App/Http/Controllers/IncidentController';
import { index as projectIndex } from '@/actions/App/Http/Controllers/ProjectController';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

function isActive(url: string): boolean {
    return page.url.startsWith(url);
}
</script>

<template>
    <div class="min-h-screen bg-slate-50 text-slate-900">
        <div class="mx-auto flex min-h-screen max-w-7xl flex-col md:flex-row">
            <aside
                class="border-b border-slate-200 bg-white p-5 md:w-64 md:border-r md:border-b-0"
            >
                <Link :href="projectIndex()" class="text-lg font-semibold"
                    >Incident Assistant</Link
                >
                <nav class="mt-6 flex gap-2 md:flex-col">
                    <Link
                        :href="projectIndex()"
                        class="rounded-lg px-3 py-2 text-sm font-medium"
                        :class="
                            isActive(projectIndex().url)
                                ? 'bg-blue-50 text-blue-700'
                                : 'text-slate-600 hover:bg-slate-100'
                        "
                    >
                        Projetos
                    </Link>
                    <Link
                        :href="incidentIndex()"
                        class="rounded-lg px-3 py-2 text-sm font-medium"
                        :class="
                            isActive(incidentIndex().url)
                                ? 'bg-blue-50 text-blue-700'
                                : 'text-slate-600 hover:bg-slate-100'
                        "
                    >
                        Incidentes
                    </Link>
                </nav>
            </aside>

            <main class="min-w-0 flex-1 p-5 sm:p-8">
                <div
                    v-if="page.props.flash.success"
                    class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                >
                    {{ page.props.flash.success }}
                </div>
                <div
                    v-if="page.props.flash.error"
                    class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
                >
                    {{ page.props.flash.error }}
                </div>
                <slot />
            </main>
        </div>
    </div>
</template>
