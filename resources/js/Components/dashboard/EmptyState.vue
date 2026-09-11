<script setup lang="ts">
import type { Component } from 'vue';

defineProps<{ title: string; description?: string; icon?: Component }>();
</script>

<template>
    <div
        class="border-border flex flex-col items-center justify-center gap-2 rounded-lg border border-dashed py-16 text-center"
    >
        <component
            :is="icon"
            v-if="icon"
            class="text-muted-foreground mb-2 h-8 w-8"
        />
        <p class="text-foreground text-sm font-medium">{{ title }}</p>
        <p v-if="description" class="text-muted-foreground max-w-sm text-sm">
            {{ description }}
        </p>
        <slot name="action" />
    </div>
</template>
