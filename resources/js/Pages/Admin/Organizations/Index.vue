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
import { Head, router } from '@inertiajs/vue3';
import { Building2 } from 'lucide-vue-next';
import { ref } from 'vue';

interface Organization {
    id: number;
    name: string;
    slug: string;
    email: string | null;
    status: string;
    currency: string;
    created_at: string;
}

const props = defineProps<{
    organizations: Paginated<Organization>;
    filters: { search?: string };
}>();

const search = ref(props.filters.search ?? '');

function applySearch() {
    router.get(
        route('admin.organizations.index'),
        { search: search.value || undefined },
        { preserveState: true, replace: true },
    );
}

function statusVariant(status: string) {
    if (status === 'active') return 'success';
    if (status === 'suspended') return 'destructive';
    return 'secondary';
}
</script>

<template>
    <Head title="Organizations" />

    <AppShell>
        <template #header>
            <h2 class="truncate text-lg font-semibold">Organizations</h2>
        </template>

        <PageHeader
            title="Organizations"
            description="Every organization registered on the platform."
        />

        <div class="mb-4">
            <input
                v-model="search"
                type="search"
                placeholder="Search by name…"
                class="border-input bg-background focus:ring-ring w-full max-w-sm rounded-md border px-3 py-2 text-sm shadow-sm focus:ring-2 focus:outline-none"
                @keyup.enter="applySearch"
            />
        </div>

        <Card v-if="organizations.data.length">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Name</TableHead>
                        <TableHead>Slug</TableHead>
                        <TableHead>Email</TableHead>
                        <TableHead>Currency</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Created</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="organization in organizations.data"
                        :key="organization.id"
                    >
                        <TableCell class="font-medium">{{
                            organization.name
                        }}</TableCell>
                        <TableCell class="text-muted-foreground">{{
                            organization.slug
                        }}</TableCell>
                        <TableCell class="text-muted-foreground">{{
                            organization.email ?? '—'
                        }}</TableCell>
                        <TableCell>{{ organization.currency }}</TableCell>
                        <TableCell>
                            <Badge
                                :variant="statusVariant(organization.status)"
                                >{{ organization.status }}</Badge
                            >
                        </TableCell>
                        <TableCell class="text-muted-foreground">{{
                            formatDate(organization.created_at)
                        }}</TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </Card>

        <EmptyState
            v-else
            title="No organizations yet"
            description="Organizations will appear here once they're created."
            :icon="Building2"
        />

        <div class="mt-4">
            <Pagination :links="organizations.links" />
        </div>
    </AppShell>
</template>
