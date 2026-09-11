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
import { Receipt } from 'lucide-vue-next';

interface Order {
    id: number;
    number: string;
    status: string;
    total_minor: number;
    currency: string;
    student: { name: string; email: string } | null;
    created_at: string;
}

defineProps<{ orders: Paginated<Order> }>();

function statusVariant(status: string) {
    if (status === 'paid') return 'success';
    if (status === 'cancelled' || status === 'expired') return 'destructive';
    if (status.includes('refunded')) return 'warning';
    return 'secondary';
}
</script>

<template>
    <Head title="Orders" />

    <AppShell>
        <template #header>
            <h2 class="truncate text-lg font-semibold">Orders</h2>
        </template>

        <PageHeader
            title="Orders"
            description="All checkout orders placed by students."
        />

        <Card v-if="orders.data.length">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Order #</TableHead>
                        <TableHead>Student</TableHead>
                        <TableHead>Total</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Created</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="order in orders.data" :key="order.id">
                        <TableCell class="font-mono text-xs">{{
                            order.number
                        }}</TableCell>
                        <TableCell>
                            <div class="font-medium">
                                {{ order.student?.name ?? '—' }}
                            </div>
                            <div class="text-muted-foreground text-xs">
                                {{ order.student?.email }}
                            </div>
                        </TableCell>
                        <TableCell>{{
                            formatMoney(order.total_minor, order.currency)
                        }}</TableCell>
                        <TableCell>
                            <Badge :variant="statusVariant(order.status)">{{
                                order.status
                            }}</Badge>
                        </TableCell>
                        <TableCell class="text-muted-foreground">{{
                            formatDate(order.created_at)
                        }}</TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </Card>

        <EmptyState
            v-else
            title="No orders yet"
            description="Orders will show up here once students check out."
            :icon="Receipt"
        />

        <div class="mt-4">
            <Pagination :links="orders.links" />
        </div>
    </AppShell>
</template>
