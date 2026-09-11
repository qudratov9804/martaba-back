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
import { formatDate, formatMoney } from '@/lib/format';
import type { Paginated } from '@/types/pagination';
import { Head } from '@inertiajs/vue3';
import { Wallet } from 'lucide-vue-next';

interface Payment {
    id: number;
    provider: string;
    status: string;
    amount_minor: number;
    currency: string;
    student: { name: string; email: string } | null;
    created_at: string;
}

defineProps<{ payments: Paginated<Payment> }>();

function statusVariant(status: string) {
    if (status === 'succeeded') return 'success';
    if (status === 'failed' || status === 'cancelled') return 'destructive';
    if (status.includes('refunded')) return 'warning';
    return 'secondary';
}
</script>

<template>
    <Head title="Payments" />

    <AppShell>
        <template #header>
            <h2 class="truncate text-lg font-semibold">Payments</h2>
        </template>

        <PageHeader
            title="Payments"
            description="Every payment attempt processed by your organization."
        />

        <Card v-if="payments.data.length">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Provider</TableHead>
                        <TableHead>Student</TableHead>
                        <TableHead>Amount</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Created</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="payment in payments.data"
                        :key="payment.id"
                    >
                        <TableCell class="capitalize">{{
                            payment.provider
                        }}</TableCell>
                        <TableCell>
                            <div class="font-medium">
                                {{ payment.student?.name ?? '—' }}
                            </div>
                            <div class="text-muted-foreground text-xs">
                                {{ payment.student?.email }}
                            </div>
                        </TableCell>
                        <TableCell>{{
                            formatMoney(payment.amount_minor, payment.currency)
                        }}</TableCell>
                        <TableCell>
                            <Badge :variant="statusVariant(payment.status)">{{
                                payment.status
                            }}</Badge>
                        </TableCell>
                        <TableCell class="text-muted-foreground">{{
                            formatDate(payment.created_at)
                        }}</TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </Card>

        <EmptyState
            v-else
            title="No payments yet"
            description="Payments will show up here once processed."
            :icon="Wallet"
        />

        <div class="mt-4">
            <Pagination :links="payments.links" />
        </div>
    </AppShell>
</template>
