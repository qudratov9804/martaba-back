<script setup lang="ts">
defineProps<{ title: string; description?: string }>();
</script>

<template>
    <div class="mb-6 flex flex-col gap-1">
        <h1 class="text-foreground text-2xl font-semibold tracking-tight">
            {{ title }}
        </h1>
        <p v-if="description" class="text-muted-foreground text-sm">
            {{ description }}
        </p>
    </div>
</template>
