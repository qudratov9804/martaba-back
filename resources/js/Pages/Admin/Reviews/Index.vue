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
import { Star } from 'lucide-vue-next';
import { ref } from 'vue';

interface Review {
    id: number;
    course_title: string;
    student_name: string;
    rating: number;
    title: string | null;
    comment: string | null;
    status: 'published' | 'hidden';
    created_at: string;
}

defineProps<{ reviews: Paginated<Review> }>();

const pendingId = ref<number | null>(null);
const errorMessage = ref('');

async function toggleStatus(review: Review) {
    pendingId.value = review.id;
    errorMessage.value = '';

    try {
        await window.axios.post(`/api/v1/admin/reviews/${review.id}/moderate`, {
            status: review.status === 'published' ? 'hidden' : 'published',
        });
        router.reload({ only: ['reviews'] });
    } catch (error) {
        errorMessage.value = apiErrorMessage(error);
    } finally {
        pendingId.value = null;
    }
}
</script>

<template>
    <Head title="Reviews" />

    <AppShell>
        <template #header>
            <h2 class="truncate text-lg font-semibold">Reviews</h2>
        </template>

        <PageHeader
            title="Reviews"
            description="Student reviews and ratings across your courses."
        />

        <p v-if="errorMessage" class="text-destructive mb-4 text-sm">
            {{ errorMessage }}
        </p>

        <Card v-if="reviews.data.length">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Course</TableHead>
                        <TableHead>Student</TableHead>
                        <TableHead>Rating</TableHead>
                        <TableHead>Comment</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Posted</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="review in reviews.data" :key="review.id">
                        <TableCell>{{ review.course_title }}</TableCell>
                        <TableCell>{{ review.student_name }}</TableCell>
                        <TableCell>
                            <span class="inline-flex items-center gap-1">
                                <Star class="h-3.5 w-3.5 fill-current" />
                                {{ review.rating }}
                            </span>
                        </TableCell>
                        <TableCell
                            class="text-muted-foreground max-w-xs truncate"
                        >
                            {{ review.comment ?? '—' }}
                        </TableCell>
                        <TableCell>
                            <Badge
                                :variant="
                                    review.status === 'published'
                                        ? 'success'
                                        : 'secondary'
                                "
                            >
                                {{ review.status }}
                            </Badge>
                        </TableCell>
                        <TableCell class="text-muted-foreground">{{
                            formatDate(review.created_at)
                        }}</TableCell>
                        <TableCell class="text-right">
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="pendingId === review.id"
                                @click="toggleStatus(review)"
                            >
                                {{
                                    review.status === 'published'
                                        ? 'Hide'
                                        : 'Publish'
                                }}
                            </Button>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </Card>

        <EmptyState
            v-else
            title="No reviews yet"
            description="Student reviews will appear here once submitted."
            :icon="Star"
        />

        <div class="mt-4">
            <Pagination :links="reviews.links" />
        </div>
    </AppShell>
</template>
