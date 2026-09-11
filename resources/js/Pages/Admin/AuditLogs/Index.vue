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
import { ScrollText } from 'lucide-vue-next';

interface AuditLog {
    id: number;
    action: string;
    subject_type: string | null;
    subject_id: number | null;
    user: { name: string } | null;
    created_at: string;
}

defineProps<{ logs: Paginated<AuditLog> }>();

function subjectLabel(log: AuditLog): string {
    if (!log.subject_type) return '—';

    const name = log.subject_type.split('\\').pop();

    return `${name} #${log.subject_id}`;
}
</script>

<template>
    <Head title="Audit Logs" />

    <AppShell>
        <template #header>
            <h2 class="truncate text-lg font-semibold">Audit Logs</h2>
        </template>

        <PageHeader
            title="Audit Logs"
            description="A record of sensitive actions taken across your organization."
        />

        <Card v-if="logs.data.length">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Action</TableHead>
                        <TableHead>Subject</TableHead>
                        <TableHead>User</TableHead>
                        <TableHead>When</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="log in logs.data" :key="log.id">
                        <TableCell
                            ><Badge variant="outline">{{
                                log.action
                            }}</Badge></TableCell
                        >
                        <TableCell class="text-muted-foreground">{{
                            subjectLabel(log)
                        }}</TableCell>
                        <TableCell>{{ log.user?.name ?? 'System' }}</TableCell>
                        <TableCell class="text-muted-foreground">{{
                            formatDate(log.created_at)
                        }}</TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </Card>

        <EmptyState
            v-else
            title="No audit activity yet"
            description="Sensitive actions will be logged here."
            :icon="ScrollText"
        />

        <div class="mt-4">
            <Pagination :links="logs.links" />
        </div>
    </AppShell>
</template>
