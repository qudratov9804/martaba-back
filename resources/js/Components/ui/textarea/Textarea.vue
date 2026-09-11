<script setup lang="ts">
import { cn } from '@/lib/utils';

const model = defineModel<string>();
const props = defineProps<{
    class?: string;
    placeholder?: string;
    id?: string;
    rows?: number;
}>();
</script>

<template>
    <textarea
        v-model="model"
        :placeholder="props.placeholder"
        :id="props.id"
        :rows="props.rows ?? 4"
        :class="
            cn(
                'border-input bg-background placeholder:text-muted-foreground focus-visible:ring-ring flex w-full rounded-md border px-3 py-2 text-sm shadow-sm transition-colors focus-visible:ring-1 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50',
                props.class,
            )
        "
    />
</template>
