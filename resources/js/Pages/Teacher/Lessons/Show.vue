<script setup lang="ts">
import ContentBlockRow from '@/Components/course-builder/ContentBlockRow.vue';
import PageHeader from '@/Components/dashboard/PageHeader.vue';
import { Button } from '@/Components/ui/button';
import { Card } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { NativeSelect } from '@/Components/ui/select';
import { Textarea } from '@/Components/ui/textarea';
import AppShell from '@/Layouts/AppShell.vue';
import { apiErrorMessage } from '@/lib/api';
import type { ContentType, Lesson } from '@/types/course';
import { Head, Link, router } from '@inertiajs/vue3';
import { AlertCircle, ArrowLeft, Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{ lesson: Lesson }>();

const errorMessage = ref<string | null>(null);

function reload() {
    router.reload({ only: ['lesson'] });
}

function showError(message: string) {
    errorMessage.value = message;
    window.setTimeout(() => (errorMessage.value = null), 6000);
}

const contents = computed(() => props.lesson.contents ?? []);

async function reorderContents(fromIndex: number, toIndex: number) {
    const ids = contents.value.map((content) => content.id);
    const [moved] = ids.splice(fromIndex, 1);
    ids.splice(toIndex, 0, moved);

    try {
        await window.axios.post(
            `/api/v1/teacher/lessons/${props.lesson.id}/contents/reorder`,
            { content_ids: ids },
        );
        reload();
    } catch (error) {
        showError(apiErrorMessage(error));
    }
}

const contentTypeOptions: { value: ContentType; label: string }[] = [
    { value: 'text', label: 'Text' },
    { value: 'video', label: 'Video file' },
    { value: 'audio', label: 'Audio file' },
    { value: 'pdf', label: 'PDF file' },
    { value: 'image', label: 'Image' },
    { value: 'download', label: 'Downloadable file' },
    { value: 'embed', label: 'Embed code' },
    { value: 'external_link', label: 'External link' },
];

const fileTypes: ContentType[] = ['video', 'audio', 'pdf', 'image', 'download'];

const showAddContent = ref(false);
const newContentType = ref<ContentType>('text');
const newTextContent = ref('');
const newEmbedCode = ref('');
const newExternalUrl = ref('');
const newFile = ref<File | null>(null);
const adding = ref(false);

const isFileType = computed(() => fileTypes.includes(newContentType.value));

function onFileChange(event: Event) {
    const target = event.target as HTMLInputElement;
    newFile.value = target.files?.[0] ?? null;
}

function resetAddForm() {
    newContentType.value = 'text';
    newTextContent.value = '';
    newEmbedCode.value = '';
    newExternalUrl.value = '';
    newFile.value = null;
    showAddContent.value = false;
}

async function addContent() {
    adding.value = true;

    try {
        let mediaId: number | null = null;

        if (isFileType.value) {
            if (!newFile.value) {
                showError('Please choose a file to upload.');
                adding.value = false;
                return;
            }

            const formData = new FormData();
            formData.append('file', newFile.value);
            formData.append('visibility', 'public');

            const uploadResponse = await window.axios.post(
                '/api/v1/media',
                formData,
                { headers: { 'Content-Type': 'multipart/form-data' } },
            );
            mediaId = uploadResponse.data.data.id;
        }

        await window.axios.post(
            `/api/v1/teacher/lessons/${props.lesson.id}/contents`,
            {
                content_type: newContentType.value,
                text_content:
                    newContentType.value === 'text'
                        ? newTextContent.value
                        : null,
                embed_code:
                    newContentType.value === 'embed'
                        ? newEmbedCode.value
                        : null,
                external_url:
                    newContentType.value === 'external_link'
                        ? newExternalUrl.value
                        : null,
                media_id: mediaId,
            },
        );

        resetAddForm();
        reload();
    } catch (error) {
        showError(apiErrorMessage(error));
    } finally {
        adding.value = false;
    }
}
</script>

<template>
    <Head :title="lesson.title" />

    <AppShell>
        <template #header>
            <h2 class="truncate text-lg font-semibold">{{ lesson.title }}</h2>
        </template>

        <Link
            :href="route('teacher.courses.show', lesson.course_id)"
            class="text-muted-foreground hover:text-foreground mb-4 inline-flex items-center gap-1.5 text-sm"
        >
            <ArrowLeft class="h-3.5 w-3.5" />
            Back to {{ lesson.course?.title ?? 'course' }}
        </Link>

        <PageHeader
            :title="lesson.title"
            :description="`Content blocks for this lesson${lesson.section ? ' · ' + lesson.section.title : ''}`"
        />

        <div
            v-if="errorMessage"
            class="border-destructive/30 bg-destructive/10 text-destructive mb-4 flex items-center gap-2 rounded-md border px-4 py-3 text-sm"
        >
            <AlertCircle class="h-4 w-4 shrink-0" />
            {{ errorMessage }}
        </div>

        <Card>
            <p
                v-if="!contents.length"
                class="text-muted-foreground px-4 py-6 text-sm"
            >
                No content blocks yet. Add text, a video, a PDF, or a link
                below.
            </p>

            <ContentBlockRow
                v-for="(content, index) in contents"
                :key="content.id"
                :content="content"
                :is-first="index === 0"
                :is-last="index === contents.length - 1"
                @move-up="reorderContents(index, index - 1)"
                @move-down="reorderContents(index, index + 1)"
                @changed="reload"
                @error="showError"
            />

            <div class="border-border border-t p-4">
                <button
                    v-if="!showAddContent"
                    type="button"
                    class="text-primary flex items-center gap-1.5 text-sm hover:underline"
                    @click="showAddContent = true"
                >
                    <Plus class="h-4 w-4" />
                    Add content block
                </button>

                <div v-else class="max-w-lg space-y-3">
                    <NativeSelect v-model="newContentType" class="h-8">
                        <option
                            v-for="option in contentTypeOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </NativeSelect>

                    <Textarea
                        v-if="newContentType === 'text'"
                        v-model="newTextContent"
                        placeholder="Lesson text content"
                        :rows="6"
                    />

                    <Textarea
                        v-else-if="newContentType === 'embed'"
                        v-model="newEmbedCode"
                        placeholder="<iframe ...></iframe>"
                        :rows="4"
                    />

                    <Input
                        v-else-if="newContentType === 'external_link'"
                        v-model="newExternalUrl"
                        type="url"
                        placeholder="https://example.com/resource"
                    />

                    <input
                        v-else-if="isFileType"
                        type="file"
                        class="text-muted-foreground block text-sm"
                        @change="onFileChange"
                    />

                    <div class="flex gap-2">
                        <Button size="sm" :disabled="adding" @click="addContent"
                            >Add</Button
                        >
                        <Button size="sm" variant="ghost" @click="resetAddForm"
                            >Cancel</Button
                        >
                    </div>
                </div>
            </div>
        </Card>
    </AppShell>
</template>
