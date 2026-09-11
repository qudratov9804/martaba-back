<script setup lang="ts">
import SectionCard from '@/Components/course-builder/SectionCard.vue';
import PageHeader from '@/Components/dashboard/PageHeader.vue';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import AppShell from '@/Layouts/AppShell.vue';
import { apiErrorMessage } from '@/lib/api';
import { formatMoney } from '@/lib/format';
import type { Course } from '@/types/course';
import { Head, Link, router } from '@inertiajs/vue3';
import { AlertCircle, ArrowLeft, Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{ course: Course }>();

const errorMessage = ref<string | null>(null);
const publishing = ref(false);

const showAddSection = ref(false);
const newSectionTitle = ref('');
const addingSection = ref(false);

const statusVariant = computed(() => {
    if (props.course.status === 'published') return 'success';
    if (props.course.status === 'archived') return 'destructive';
    return 'secondary';
});

function reload() {
    router.reload({ only: ['course'] });
}

function showError(message: string) {
    errorMessage.value = message;
    window.setTimeout(() => (errorMessage.value = null), 6000);
}

async function togglePublish() {
    publishing.value = true;
    const action =
        props.course.status === 'published' ? 'unpublish' : 'publish';

    try {
        await window.axios.post(
            `/api/v1/teacher/courses/${props.course.id}/${action}`,
        );
        reload();
    } catch (error) {
        showError(apiErrorMessage(error));
    } finally {
        publishing.value = false;
    }
}

async function addSection() {
    if (!newSectionTitle.value.trim()) return;

    addingSection.value = true;

    try {
        await window.axios.post(
            `/api/v1/teacher/courses/${props.course.id}/sections`,
            {
                title: newSectionTitle.value,
            },
        );
        newSectionTitle.value = '';
        showAddSection.value = false;
        reload();
    } catch (error) {
        showError(apiErrorMessage(error));
    } finally {
        addingSection.value = false;
    }
}

async function reorderSections(fromIndex: number, toIndex: number) {
    const ids = props.course.sections.map((section) => section.id);
    const [moved] = ids.splice(fromIndex, 1);
    ids.splice(toIndex, 0, moved);

    try {
        await window.axios.post(
            `/api/v1/teacher/courses/${props.course.id}/sections/reorder`,
            { section_ids: ids },
        );
        reload();
    } catch (error) {
        showError(apiErrorMessage(error));
    }
}
</script>

<template>
    <Head :title="course.title" />

    <AppShell>
        <template #header>
            <h2 class="truncate text-lg font-semibold">{{ course.title }}</h2>
        </template>

        <Link
            :href="route('teacher.courses.index')"
            class="text-muted-foreground hover:text-foreground mb-4 inline-flex items-center gap-1.5 text-sm"
        >
            <ArrowLeft class="h-3.5 w-3.5" />
            Back to My Courses
        </Link>

        <PageHeader
            :title="course.title"
            :description="course.short_description ?? undefined"
        >
            <template #actions>
                <Badge :variant="statusVariant">{{ course.status }}</Badge>
                <Button :disabled="publishing" @click="togglePublish">
                    {{
                        course.status === 'published' ? 'Unpublish' : 'Publish'
                    }}
                </Button>
            </template>
        </PageHeader>

        <div
            v-if="errorMessage"
            class="border-destructive/30 bg-destructive/10 text-destructive mb-4 flex items-center gap-2 rounded-md border px-4 py-3 text-sm"
        >
            <AlertCircle class="h-4 w-4 shrink-0" />
            {{ errorMessage }}
        </div>

        <div class="text-muted-foreground mb-6 flex flex-wrap gap-4 text-sm">
            <span>{{
                course.pricing_type === 'free'
                    ? 'Free'
                    : formatMoney(course.price_minor, course.currency)
            }}</span>
            <span>·</span>
            <span
                >{{ course.sections.length }} section{{
                    course.sections.length === 1 ? '' : 's'
                }}</span
            >
        </div>

        <div class="space-y-4">
            <SectionCard
                v-for="(section, index) in course.sections"
                :key="section.id"
                :section="section"
                :is-first="index === 0"
                :is-last="index === course.sections.length - 1"
                @move-up="reorderSections(index, index - 1)"
                @move-down="reorderSections(index, index + 1)"
                @changed="reload"
                @error="showError"
            />

            <div class="border-border rounded-lg border border-dashed p-4">
                <button
                    v-if="!showAddSection"
                    type="button"
                    class="text-primary flex items-center gap-1.5 text-sm hover:underline"
                    @click="showAddSection = true"
                >
                    <Plus class="h-4 w-4" />
                    Add section
                </button>

                <div v-else class="flex flex-wrap items-center gap-2">
                    <Input
                        v-model="newSectionTitle"
                        placeholder="Section title"
                        class="h-8 max-w-sm"
                        @keyup.enter="addSection"
                    />
                    <Button
                        size="sm"
                        :disabled="addingSection"
                        @click="addSection"
                        >Add</Button
                    >
                    <Button
                        size="sm"
                        variant="ghost"
                        @click="showAddSection = false"
                        >Cancel</Button
                    >
                </div>
            </div>
        </div>
    </AppShell>
</template>
