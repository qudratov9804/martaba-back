<script setup lang="ts">
import type { PageProps } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { CheckCircle2, XCircle } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage<PageProps>();

const success = computed(() => page.props.flash?.success);
const error = computed(() => page.props.flash?.error);
</script>

<template>
    <div v-if="success || error" class="mb-4">
        <div
            v-if="success"
            class="flex items-center gap-2 rounded-md border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-400"
        >
            <CheckCircle2 class="h-4 w-4 shrink-0" />
            {{ success }}
        </div>
        <div
            v-if="error"
            class="border-destructive/30 bg-destructive/10 text-destructive flex items-center gap-2 rounded-md border px-4 py-3 text-sm"
        >
            <XCircle class="h-4 w-4 shrink-0" />
            {{ error }}
        </div>
    </div>
</template>
