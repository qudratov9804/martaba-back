<script setup lang="ts">
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Textarea } from '@/Components/ui/textarea';
import { apiErrorMessage } from '@/lib/api';
import { formatBytes } from '@/lib/format';
import type { ContentBlock } from '@/types/course';
import {
    ArrowDown,
    ArrowUp,
    FileText,
    Image as ImageIcon,
    Link2,
    Music,
    Pencil,
    Trash2,
    Video,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    content: ContentBlock;
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
    switch (props.content.content_type) {
        case 'video':
            return Video;
        case 'audio':
            return Music;
        case 'pdf':
        case 'download':
            return FileText;
        case 'image':
            return ImageIcon;
        case 'embed':
        case 'external_link':
            return Link2;
        default:
            return FileText;
    }
});

const typeLabel = computed(() => props.content.content_type.replace('_', ' '));

const editingText = ref(false);
const textDraft = ref(props.content.text_content ?? '');
const savingText = ref(false);

async function saveText() {
    savingText.value = true;

    try {
        await window.axios.put(`/api/v1/teacher/contents/${props.content.id}`, {
            content_type: props.content.content_type,
            text_content: textDraft.value,
        });
        editingText.value = false;
        emit('changed');
    } catch (error) {
        emit('error', apiErrorMessage(error));
    } finally {
        savingText.value = false;
    }
}

async function destroy() {
    if (!confirm('Delete this content block? This cannot be undone.')) {
        return;
    }

    try {
        await window.axios.delete(
            `/api/v1/teacher/contents/${props.content.id}`,
        );
        emit('changed');
    } catch (error) {
        emit('error', apiErrorMessage(error));
    }
}
</script>

<template>
    <div
        class="border-border flex items-start justify-between gap-3 border-t px-4 py-3 first:border-t-0"
    >
        <div class="flex min-w-0 flex-1 items-start gap-3">
            <component
                :is="icon"
                class="text-muted-foreground mt-0.5 h-4 w-4 shrink-0"
            />

            <div class="min-w-0 flex-1 space-y-1.5">
                <Badge variant="outline" class="capitalize">{{
                    typeLabel
                }}</Badge>

                <template v-if="content.content_type === 'text'">
                    <div v-if="editingText" class="space-y-2">
                        <Textarea v-model="textDraft" :rows="5" />
                        <div class="flex gap-2">
                            <Button
                                size="sm"
                                :disabled="savingText"
                                @click="saveText"
                                >Save</Button
                            >
                            <Button
                                size="sm"
                                variant="ghost"
                                @click="editingText = false"
                                >Cancel</Button
                            >
                        </div>
                    </div>
                    <p
                        v-else
                        class="text-foreground text-sm whitespace-pre-wrap"
                    >
                        {{ content.text_content }}
                    </p>
                </template>

                <template v-else-if="content.media">
                    <a
                        v-if="content.media.url"
                        :href="content.media.url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="text-primary text-sm hover:underline"
                        >{{ content.media.original_name }}</a
                    >
                    <span v-else class="text-foreground text-sm">{{
                        content.media.original_name
                    }}</span>
                    <span class="text-muted-foreground ml-2 text-xs">{{
                        formatBytes(content.media.size)
                    }}</span>
                </template>

                <template v-else-if="content.content_type === 'external_link'">
                    <a
                        :href="content.external_url ?? '#'"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="text-primary text-sm break-all hover:underline"
                        >{{ content.external_url }}</a
                    >
                </template>

                <template v-else-if="content.content_type === 'embed'">
                    <code
                        class="bg-muted text-muted-foreground block truncate rounded px-2 py-1 text-xs"
                        >{{ content.embed_code }}</code
                    >
                </template>

                <p v-else class="text-muted-foreground text-sm italic">
                    No file attached.
                </p>
            </div>
        </div>

        <div class="flex shrink-0 items-center gap-1">
            <button
                v-if="content.content_type === 'text' && !editingText"
                type="button"
                class="text-muted-foreground hover:bg-accent rounded p-1"
                title="Edit text"
                @click="editingText = true"
            >
                <Pencil class="h-3.5 w-3.5" />
            </button>
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
                title="Delete content block"
                @click="destroy"
            >
                <Trash2 class="h-3.5 w-3.5" />
            </button>
        </div>
    </div>
</template>
