<script setup lang="ts">
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{ class?: string; orientation?: 'horizontal' | 'vertical' }>(),
    { orientation: 'horizontal' },
);
</script>

<template>
    <div
        role="separator"
        :class="
            cn(
                'bg-border shrink-0',
                orientation === 'horizontal' ? 'h-px w-full' : 'h-full w-px',
                props.class,
            )
        "
    />
</template>
