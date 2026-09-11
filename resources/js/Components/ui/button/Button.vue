<script setup lang="ts">
import { cn } from '@/lib/utils';
import { type ButtonVariants, buttonVariants } from '.';

interface Props {
    variant?: ButtonVariants['variant'];
    size?: ButtonVariants['size'];
    as?: string;
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
