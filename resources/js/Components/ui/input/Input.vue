<script setup lang="ts">
import { cn } from '@/lib/utils';

const model = defineModel<string | number>();
const props = defineProps<{
    class?: string;
    type?: string;
    placeholder?: string;
    id?: string;
}>();
</script>

<template>
    <input
        v-model="model"
        :type="props.type ?? 'text'"
        :placeholder="props.placeholder"
        :id="props.id"
        :class="
            cn(
                'border-input bg-background placeholder:text-muted-foreground focus-visible:ring-ring flex h-9 w-full rounded-md border px-3 py-1 text-sm shadow-sm transition-colors focus-visible:ring-1 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50',
                props.class,
            )
        "
    />
</template>
