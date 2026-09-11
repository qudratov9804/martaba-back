<script setup lang="ts">
import { Badge } from '@/Components/ui/badge';
import { apiErrorMessage } from '@/lib/api';
import type { Lesson } from '@/types/course';
import {
    ArrowDown,
    ArrowUp,
    Code,
    Download,
    FileText,
    Image as ImageIcon,
    Link2,
    Music,
    Presentation,
    Radio,
    Trash2,
    Video,
} from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    lesson: Lesson;
    isFirst: boolean;
    isLast: boolean;
}>();
const emit = defineEmits<{
    'move-up': [];
    'move-down': [];
    changed: [];
    error: [message: string];
}>();

const icon = computed(() => {
    switch (props.lesson.lesson_type) {
        case 'video':
        case 'live':
            return Video;
        case 'audio':
            return Music;
        case 'pdf':
        case 'document':
            return FileText;
        case 'presentation':
            return Presentation;
        case 'image':
            return ImageIcon;
        case 'embed':
        case 'external_link':
            return Link2;
        case 'download':
            return Download;
        case 'code':
        case 'html':
            return Code;
        case 'quiz':
        case 'assignment':
            return Radio;
        default:
            return FileText;
    }
});

async function destroy() {
    if (
        !confirm(
            `Delete lesson "${props.lesson.title}"? This cannot be undone.`,
        )
    ) {
        return;
    }

    try {
        await window.axios.delete(`/api/v1/teacher/lessons/${props.lesson.id}`);
        emit('changed');
    } catch (error) {
        emit('error', apiErrorMessage(error));
    }
}
</script>

<template>
    <div
        class="border-border flex items-center justify-between gap-3 border-t px-4 py-3 first:border-t-0"
    >
        <div class="flex min-w-0 items-center gap-3">
            <component
                :is="icon"
                class="text-muted-foreground h-4 w-4 shrink-0"
            />
            <span class="text-foreground truncate text-sm">{{
                lesson.title
            }}</span>
            <Badge v-if="lesson.is_preview" variant="secondary">Preview</Badge>
            <Badge v-if="!lesson.is_required" variant="outline">Optional</Badge>
        </div>

        <div class="flex shrink-0 items-center gap-1">
            <button
                type="button"
                class="text-muted-foreground hover:bg-accent rounded p-1 disabled:opacity-30"
                :disabled="isFirst"
                title="Move up"
                @click="emit('move-up')"
            >
                <ArrowUp class="h-3.5 w-3.5" />
            </button>
            <button
                type="button"
                class="text-muted-foreground hover:bg-accent rounded p-1 disabled:opacity-30"
                :disabled="isLast"
                title="Move down"
                @click="emit('move-down')"
            >
                <ArrowDown class="h-3.5 w-3.5" />
            </button>
            <button
                type="button"
                class="text-muted-foreground hover:bg-destructive/10 hover:text-destructive rounded p-1"
                title="Delete lesson"
                @click="destroy"
            >
                <Trash2 class="h-3.5 w-3.5" />
            </button>
        </div>
    </div>
</template>
