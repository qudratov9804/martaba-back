<script setup lang="ts">
import { cn } from '@/lib/utils';

const props = defineProps<{ class?: string }>();
</script>

<template>
    <th
        :class="
            cn(
                'text-muted-foreground h-10 px-2 text-left align-middle font-medium [&:has([role=checkbox])]:pr-0',
                props.class,
            )
        "
    >
        <slot />
    </th>
</template>
