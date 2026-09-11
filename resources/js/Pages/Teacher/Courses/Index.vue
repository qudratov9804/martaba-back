<script setup lang="ts">
import EmptyState from '@/Components/dashboard/EmptyState.vue';
import PageHeader from '@/Components/dashboard/PageHeader.vue';
import Pagination from '@/Components/dashboard/Pagination.vue';
import { Badge } from '@/Components/ui/badge';
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
import { formatMoney } from '@/lib/format';
import type { Paginated } from '@/types/pagination';
import { Head } from '@inertiajs/vue3';
import { BookOpen } from 'lucide-vue-next';

interface Course {
    id: number;
    title: string;
    status: string;
    pricing_type: string;
    price_minor: number;
    currency: string;
    lessons_count: number;
}

defineProps<{ courses: Paginated<Course> }>();

function statusVariant(status: string) {
    if (status === 'published') return 'success';
    if (status === 'archived') return 'destructive';
    return 'secondary';
}
</script>

<template>
    <Head title="My Courses" />

    <AppShell>
        <template #header>
            <h2 class="truncate text-lg font-semibold">My Courses</h2>
        </template>

        <PageHeader
            title="My Courses"
            description="Courses you created or co-instruct."
        />

        <Card v-if="courses.data.length">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Title</TableHead>
                        <TableHead>Pricing</TableHead>
                        <TableHead>Lessons</TableHead>
                        <TableHead>Status</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="course in courses.data" :key="course.id">
                        <TableCell class="font-medium">{{
                            course.title
                        }}</TableCell>
                        <TableCell class="text-muted-foreground">
                            {{
                                course.pricing_type === 'free'
                                    ? 'Free'
                                    : formatMoney(
                                          course.price_minor,
                                          course.currency,
                                      )
                            }}
                        </TableCell>
                        <TableCell class="text-muted-foreground">{{
                            course.lessons_count
                        }}</TableCell>
                        <TableCell>
                            <Badge :variant="statusVariant(course.status)">{{
                                course.status
                            }}</Badge>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </Card>

        <EmptyState
            v-else
            title="No courses yet"
            description="Courses you create will show up here."
            :icon="BookOpen"
        />

        <div class="mt-4">
            <Pagination :links="courses.links" />
        </div>
    </AppShell>
</template>
