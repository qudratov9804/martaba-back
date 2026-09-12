<script setup lang="ts">
import EmptyState from '@/Components/dashboard/EmptyState.vue';
import PageHeader from '@/Components/dashboard/PageHeader.vue';
import Pagination from '@/Components/dashboard/Pagination.vue';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card } from '@/Components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import AppShell from '@/Layouts/AppShell.vue';
import { apiErrorMessage } from '@/lib/api';
import { formatDate } from '@/lib/format';
import type { Paginated } from '@/types/pagination';
import { Head, router } from '@inertiajs/vue3';
import { BookOpen } from 'lucide-vue-next';
import { ref } from 'vue';

interface AdminCourse {
    id: number;
    title: string;
    status: string;
    pricing_type: string;
    created_at: string;
}

defineProps<{ courses: Paginated<AdminCourse> }>();

const pendingId = ref<number | null>(null);
const errorMessage = ref('');

const statusVariant: Record<string, 'success' | 'secondary' | 'destructive'> = {
    published: 'success',
    draft: 'secondary',
    review: 'secondary',
    unpublished: 'secondary',
    archived: 'destructive',
};

async function publish(course: AdminCourse) {
    pendingId.value = course.id;
    errorMessage.value = '';

    try {
        await window.axios.post(`/api/v1/admin/courses/${course.id}/publish`);
        router.reload({ only: ['courses'] });
    } catch (error) {
        errorMessage.value = apiErrorMessage(error);
    } finally {
        pendingId.value = null;
    }
}

async function archive(course: AdminCourse) {
    if (!confirm(`Archive "${course.title}"? Students will lose access.`)) {
        return;
    }

    pendingId.value = course.id;
    errorMessage.value = '';

    try {
        await window.axios.post(`/api/v1/admin/courses/${course.id}/archive`);
        router.reload({ only: ['courses'] });
    } catch (error) {
        errorMessage.value = apiErrorMessage(error);
    } finally {
        pendingId.value = null;
    }
}
</script>

<template>
    <Head title="Courses" />

    <AppShell>
        <template #header>
            <h2 class="truncate text-lg font-semibold">Courses</h2>
        </template>

        <PageHeader
            title="Courses"
            description="Moderate courses across your organization."
        />

        <p v-if="errorMessage" class="text-destructive mb-4 text-sm">
            {{ errorMessage }}
        </p>

        <Card v-if="courses.data.length">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Title</TableHead>
                        <TableHead>Pricing</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Created</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="course in courses.data" :key="course.id">
                        <TableCell class="font-medium">{{
                            course.title
                        }}</TableCell>
                        <TableCell class="text-muted-foreground">{{
                            course.pricing_type
                        }}</TableCell>
                        <TableCell>
                            <Badge
                                :variant="
                                    statusVariant[course.status] ?? 'secondary'
                                "
                            >
                                {{ course.status }}
                            </Badge>
                        </TableCell>
                        <TableCell class="text-muted-foreground">{{
                            formatDate(course.created_at)
                        }}</TableCell>
                        <TableCell class="space-x-2 text-right">
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="
                                    pendingId === course.id ||
                                    course.status === 'published'
                                "
                                @click="publish(course)"
                            >
                                Publish
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="
                                    pendingId === course.id ||
                                    course.status === 'archived'
                                "
                                @click="archive(course)"
                            >
                                Archive
                            </Button>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </Card>

        <EmptyState
            v-else
            title="No courses yet"
            description="Courses created by teachers will appear here."
            :icon="BookOpen"
        />

        <div class="mt-4">
            <Pagination :links="courses.links" />
        </div>
    </AppShell>
</template>
