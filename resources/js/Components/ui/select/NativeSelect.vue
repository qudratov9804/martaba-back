<script setup lang="ts">
import { cn } from '@/lib/utils';

const model = defineModel<string | number | null>();
const props = defineProps<{ class?: string; id?: string }>();
</script>

<template>
    <select
        v-model="model"
        :id="props.id"
        :class="
            cn(
                'border-input bg-background focus-visible:ring-ring flex h-9 w-full rounded-md border px-3 py-1 text-sm shadow-sm transition-colors focus-visible:ring-1 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50',
                props.class,
            )
        "
    >
        <slot />
    </select>
</template>
