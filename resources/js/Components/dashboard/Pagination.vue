<script setup lang="ts">
import type { PaginationLink } from '@/types/pagination';
import { Link } from '@inertiajs/vue3';

defineProps<{ links: PaginationLink[] }>();

function decodeLabel(label: string): string {
    return label
        .replace(/&laquo;/g, '«')
        .replace(/&raquo;/g, '»')
        .replace(/&amp;/g, '&');
}
</script>

<template>
    <nav v-if="links.length > 3" class="flex flex-wrap items-center gap-1">
        <template v-for="(link, index) in links" :key="index">
            <span
                v-if="!link.url"
                class="text-muted-foreground rounded-md px-3 py-1.5 text-sm"
            >
                {{ decodeLabel(link.label) }}
            </span>
            <Link
                v-else
                :href="link.url"
                preserve-scroll
                :class="[
                    'rounded-md px-3 py-1.5 text-sm transition-colors',
                    link.active
                        ? 'bg-primary text-primary-foreground'
                        : 'text-foreground hover:bg-accent',
                ]"
            >
                {{ decodeLabel(link.label) }}
            </Link>
        </template>
    </nav>
</template>
