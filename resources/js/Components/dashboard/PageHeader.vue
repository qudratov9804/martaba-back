<script setup lang="ts">
defineProps<{ title: string; description?: string }>();
</script>

<template>
    <div
        class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center"
    >
        <div class="flex flex-col gap-1">
            <h1 class="text-foreground text-2xl font-semibold tracking-tight">
                {{ title }}
            </h1>
            <p v-if="description" class="text-muted-foreground text-sm">
                {{ description }}
            </p>
        </div>
        <div v-if="$slots.actions" class="flex shrink-0 items-center gap-2">
            <slot name="actions" />
        </div>
    </div>
</template>
