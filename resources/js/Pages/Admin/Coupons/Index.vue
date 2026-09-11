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
import { Percent } from 'lucide-vue-next';

interface Coupon {
    id: number;
    code: string;
    name: string;
    type: string;
    value: number;
    status: string;
    scope_type: string;
    created_at: string;
}

defineProps<{ coupons: Paginated<Coupon> }>();

function valueLabel(coupon: Coupon): string {
    return coupon.type === 'percentage'
        ? `${coupon.value}%`
        : `${coupon.value} (fixed)`;
}
</script>

<template>
    <Head title="Coupons" />

    <AppShell>
        <template #header>
            <h2 class="truncate text-lg font-semibold">Coupons</h2>
        </template>

        <PageHeader
            title="Coupons"
            description="Discount codes available for checkout."
        />

        <Card v-if="coupons.data.length">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Code</TableHead>
                        <TableHead>Name</TableHead>
                        <TableHead>Value</TableHead>
                        <TableHead>Scope</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Created</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="coupon in coupons.data" :key="coupon.id">
                        <TableCell class="font-mono font-medium">{{
                            coupon.code
                        }}</TableCell>
                        <TableCell>{{ coupon.name }}</TableCell>
                        <TableCell>{{ valueLabel(coupon) }}</TableCell>
                        <TableCell class="text-muted-foreground">{{
                            coupon.scope_type
                        }}</TableCell>
                        <TableCell>
                            <Badge
                                :variant="
                                    coupon.status === 'active'
                                        ? 'success'
                                        : 'secondary'
                                "
                            >
                                {{ coupon.status }}
                            </Badge>
                        </TableCell>
                        <TableCell class="text-muted-foreground">{{
                            formatDate(coupon.created_at)
                        }}</TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </Card>

        <EmptyState
            v-else
            title="No coupons yet"
            description="Create a coupon to offer discounts at checkout."
            :icon="Percent"
        />

        <div class="mt-4">
            <Pagination :links="coupons.links" />
        </div>
    </AppShell>
</template>
