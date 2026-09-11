<script setup lang="ts">
import { cn } from '@/lib/utils';

const props = defineProps<{ class?: string }>();
</script>

<template>
    <span
        :class="
            cn(
                'bg-muted text-muted-foreground relative flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full text-sm font-medium',
                props.class,
            )
        "
    >
        <slot />
    </span>
</template>
