<script setup lang="ts">
import LessonRow from '@/Components/course-builder/LessonRow.vue';
import { Button } from '@/Components/ui/button';
import { Card } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { NativeSelect } from '@/Components/ui/select';
import { apiErrorMessage } from '@/lib/api';
import { slugify } from '@/lib/slug';
import type { LessonType, Section } from '@/types/course';
import {
    ArrowDown,
    ArrowUp,
    ChevronDown,
    ChevronRight,
    Pencil,
    Plus,
    Trash2,
} from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    section: Section;
    isFirst: boolean;
    isLast: boolean;
}>();
const emit = defineEmits<{
    'move-up': [];
    'move-down': [];
    changed: [];
    error: [message: string];
}>();

const expanded = ref(true);
const editingTitle = ref(false);
const titleDraft = ref(props.section.title);
const savingTitle = ref(false);

const showAddLesson = ref(false);
const newLessonTitle = ref('');
const newLessonType = ref<LessonType>('text');
const addingLesson = ref(false);

const lessonTypeOptions: LessonType[] = [
    'text',
    'video',
    'audio',
    'pdf',
    'document',
    'presentation',
    'image',
    'embed',
    'quiz',
    'assignment',
    'live',
    'external_link',
    'download',
    'code',
    'html',
];

async function saveTitle() {
    if (!titleDraft.value.trim()) {
        titleDraft.value = props.section.title;
        editingTitle.value = false;
        return;
    }

    savingTitle.value = true;

    try {
        await window.axios.put(`/api/v1/teacher/sections/${props.section.id}`, {
            title: titleDraft.value,
        });
        editingTitle.value = false;
        emit('changed');
    } catch (error) {
        emit('error', apiErrorMessage(error));
    } finally {
        savingTitle.value = false;
    }
}

async function deleteSection() {
    if (
        !confirm(
            `Delete section "${props.section.title}" and all of its lessons? This cannot be undone.`,
        )
    ) {
        return;
    }

    try {
        await window.axios.delete(
            `/api/v1/teacher/sections/${props.section.id}`,
        );
        emit('changed');
    } catch (error) {
        emit('error', apiErrorMessage(error));
    }
}

async function addLesson() {
    if (!newLessonTitle.value.trim()) return;

    addingLesson.value = true;

    try {
        await window.axios.post(
            `/api/v1/teacher/sections/${props.section.id}/lessons`,
            {
                title: newLessonTitle.value,
                slug: slugify(newLessonTitle.value),
                lesson_type: newLessonType.value,
            },
        );
        newLessonTitle.value = '';
        newLessonType.value = 'text';
        showAddLesson.value = false;
        emit('changed');
    } catch (error) {
        emit('error', apiErrorMessage(error));
    } finally {
        addingLesson.value = false;
    }
}

async function reorderLessons(fromIndex: number, toIndex: number) {
    const ids = props.section.lessons.map((lesson) => lesson.id);
    const [moved] = ids.splice(fromIndex, 1);
    ids.splice(toIndex, 0, moved);

    try {
        await window.axios.post(
            `/api/v1/teacher/sections/${props.section.id}/lessons/reorder`,
            { lesson_ids: ids },
        );
        emit('changed');
    } catch (error) {
        emit('error', apiErrorMessage(error));
    }
}
</script>

<template>
    <Card>
        <div class="flex items-center justify-between gap-3 px-4 py-3">
            <div class="flex min-w-0 flex-1 items-center gap-2">
                <button
                    type="button"
                    class="text-muted-foreground hover:text-foreground"
                    @click="expanded = !expanded"
                >
                    <component
                        :is="expanded ? ChevronDown : ChevronRight"
                        class="h-4 w-4"
                    />
                </button>

                <template v-if="editingTitle">
                    <Input
                        v-model="titleDraft"
                        class="h-8 max-w-sm"
                        @keyup.enter="saveTitle"
                        @keyup.escape="editingTitle = false"
                    />
                    <Button size="sm" :disabled="savingTitle" @click="saveTitle"
                        >Save</Button
                    >
                </template>
                <span
                    v-else
                    class="text-foreground truncate text-sm font-semibold"
                    >{{ section.title }}</span
                >
            </div>

            <div class="flex shrink-0 items-center gap-1">
                <button
                    v-if="!editingTitle"
                    type="button"
                    class="text-muted-foreground hover:bg-accent rounded p-1.5"
                    title="Rename section"
                    @click="editingTitle = true"
                >
                    <Pencil class="h-3.5 w-3.5" />
                </button>
                <button
                    type="button"
                    class="text-muted-foreground hover:bg-accent rounded p-1.5 disabled:opacity-30"
                    :disabled="isFirst"
                    title="Move section up"
                    @click="emit('move-up')"
                >
                    <ArrowUp class="h-3.5 w-3.5" />
                </button>
                <button
                    type="button"
                    class="text-muted-foreground hover:bg-accent rounded p-1.5 disabled:opacity-30"
                    :disabled="isLast"
                    title="Move section down"
                    @click="emit('move-down')"
                >
                    <ArrowDown class="h-3.5 w-3.5" />
                </button>
                <button
                    type="button"
                    class="text-muted-foreground hover:bg-destructive/10 hover:text-destructive rounded p-1.5"
                    title="Delete section"
                    @click="deleteSection"
                >
                    <Trash2 class="h-3.5 w-3.5" />
                </button>
            </div>
        </div>

        <div v-if="expanded" class="border-border border-t">
            <p
                v-if="!section.lessons.length"
                class="text-muted-foreground px-4 py-4 text-sm"
            >
                No lessons yet.
            </p>

            <LessonRow
                v-for="(lesson, index) in section.lessons"
                :key="lesson.id"
                :lesson="lesson"
                :is-first="index === 0"
                :is-last="index === section.lessons.length - 1"
                @move-up="reorderLessons(index, index - 1)"
                @move-down="reorderLessons(index, index + 1)"
                @changed="emit('changed')"
                @error="(message) => emit('error', message)"
            />

            <div class="border-border border-t p-3">
                <button
                    v-if="!showAddLesson"
                    type="button"
                    class="text-primary flex items-center gap-1.5 text-sm hover:underline"
                    @click="showAddLesson = true"
                >
                    <Plus class="h-4 w-4" />
                    Add lesson
                </button>

                <div v-else class="flex flex-wrap items-center gap-2">
                    <Input
                        v-model="newLessonTitle"
                        placeholder="Lesson title"
                        class="h-8 max-w-xs"
                        @keyup.enter="addLesson"
                    />
                    <NativeSelect v-model="newLessonType" class="h-8 w-36">
                        <option
                            v-for="type in lessonTypeOptions"
                            :key="type"
                            :value="type"
                        >
                            {{ type }}
                        </option>
                    </NativeSelect>
                    <Button
                        size="sm"
                        :disabled="addingLesson"
                        @click="addLesson"
                        >Add</Button
                    >
                    <Button
                        size="sm"
                        variant="ghost"
                        @click="showAddLesson = false"
                        >Cancel</Button
                    >
                </div>
            </div>
        </div>
    </Card>
</template>
