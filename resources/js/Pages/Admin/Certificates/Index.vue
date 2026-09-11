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
import { formatDate } from '@/lib/format';
import type { Paginated } from '@/types/pagination';
import { Head } from '@inertiajs/vue3';
import { Award } from 'lucide-vue-next';

interface Certificate {
    id: number;
    certificate_number: string;
    student_name: string;
    course_name: string;
    status: string;
    issued_at: string;
}

defineProps<{ certificates: Paginated<Certificate> }>();
</script>

<template>
    <Head title="Certificates" />

    <AppShell>
        <template #header>
            <h2 class="truncate text-lg font-semibold">Certificates</h2>
        </template>

        <PageHeader
            title="Certificates"
            description="All certificates issued to students."
        />

        <Card v-if="certificates.data.length">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Certificate #</TableHead>
                        <TableHead>Student</TableHead>
                        <TableHead>Course</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Issued</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="certificate in certificates.data"
                        :key="certificate.id"
                    >
                        <TableCell class="font-mono text-xs">{{
                            certificate.certificate_number
                        }}</TableCell>
                        <TableCell>{{ certificate.student_name }}</TableCell>
                        <TableCell>{{ certificate.course_name }}</TableCell>
                        <TableCell>
                            <Badge
                                :variant="
                                    certificate.status === 'issued'
                                        ? 'success'
                                        : 'destructive'
                                "
                            >
                                {{ certificate.status }}
                            </Badge>
                        </TableCell>
                        <TableCell class="text-muted-foreground">{{
                            formatDate(certificate.issued_at)
                        }}</TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </Card>

        <EmptyState
            v-else
            title="No certificates yet"
            description="Certificates will appear here once students complete courses."
            :icon="Award"
        />

        <div class="mt-4">
            <Pagination :links="certificates.links" />
        </div>
    </AppShell>
</template>
