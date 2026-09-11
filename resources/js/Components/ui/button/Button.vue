<script setup lang="ts">
import { cn } from '@/lib/utils';
import type { Component } from 'vue';
import { type ButtonVariants, buttonVariants } from '.';

interface Props {
    variant?: ButtonVariants['variant'];
    size?: ButtonVariants['size'];
    as?: string | Component;
    class?: string;
}

const props = withDefaults(defineProps<Props>(), {
    as: 'button',
});
</script>

<template>
    <component
        :is="as"
        :class="cn(buttonVariants({ variant, size }), props.class)"
    >
        <slot />
    </component>
</template>
