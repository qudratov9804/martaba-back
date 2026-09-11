<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import type { Component } from 'vue';

withDefaults(
    defineProps<{
        title: string;
        value: string | number;
        icon?: Component;
        hint?: string;
    }>(),
    {},
);
</script>

<template>
    <Card>
        <CardHeader
            class="flex flex-row items-center justify-between space-y-0 pb-2"
        >
            <CardTitle>{{ title }}</CardTitle>
            <component
                :is="icon"
                v-if="icon"
                class="text-muted-foreground h-4 w-4"
            />
        </CardHeader>
        <CardContent>
            <div class="text-foreground text-2xl font-bold">{{ value }}</div>
            <p v-if="hint" class="text-muted-foreground mt-1 text-xs">
                {{ hint }}
            </p>
        </CardContent>
    </Card>
</template>
