<script setup lang="ts">
import { Label } from '@/Components/ui/label';

defineProps<{ label: string; for: string; error?: string; hint?: string }>();
</script>

<template>
    <div class="space-y-1.5">
        <Label :for="$props.for">{{ label }}</Label>
        <slot />
        <p v-if="hint && !error" class="text-muted-foreground text-xs">
            {{ hint }}
        </p>
        <p v-if="error" class="text-destructive text-xs">{{ error }}</p>
    </div>
</template>
