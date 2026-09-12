<script setup lang="ts">
import EmptyState from '@/Components/dashboard/EmptyState.vue';
import PageHeader from '@/Components/dashboard/PageHeader.vue';
import Pagination from '@/Components/dashboard/Pagination.vue';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card } from '@/Components/ui/card';
import { NativeSelect } from '@/Components/ui/select';
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
import { Users as UsersIcon } from 'lucide-vue-next';
import { ref } from 'vue';

interface AdminUser {
    id: number;
    name: string;
    email: string;
    roles: string[];
    deleted_at: string | null;
    created_at: string;
}

defineProps<{ users: Paginated<AdminUser> }>();

const roleOptions = ['super_admin', 'teacher', 'student'];
const pendingId = ref<number | null>(null);
const errorMessage = ref('');

async function changeRole(user: AdminUser, role: string) {
    pendingId.value = user.id;
    errorMessage.value = '';

    try {
        await window.axios.put(`/api/v1/admin/users/${user.id}`, { role });
        router.reload({ only: ['users'] });
    } catch (error) {
        errorMessage.value = apiErrorMessage(error);
    } finally {
        pendingId.value = null;
    }
}

async function deactivate(user: AdminUser) {
    if (
        !confirm(
            `Deactivate ${user.name}? They will no longer be able to sign in.`,
        )
    ) {
        return;
    }

    pendingId.value = user.id;
    errorMessage.value = '';

    try {
        await window.axios.delete(`/api/v1/admin/users/${user.id}`);
        router.reload({ only: ['users'] });
    } catch (error) {
        errorMessage.value = apiErrorMessage(error);
    } finally {
        pendingId.value = null;
    }
}
</script>

<template>
    <Head title="Users" />

    <AppShell>
        <template #header>
            <h2 class="truncate text-lg font-semibold">Users</h2>
        </template>

        <PageHeader
            title="Users"
            description="Manage teachers and students in your organization."
        />

        <p v-if="errorMessage" class="text-destructive mb-4 text-sm">
            {{ errorMessage }}
        </p>

        <Card v-if="users.data.length">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Name</TableHead>
                        <TableHead>Email</TableHead>
                        <TableHead>Role</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Joined</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="user in users.data" :key="user.id">
                        <TableCell class="font-medium">{{
                            user.name
                        }}</TableCell>
                        <TableCell class="text-muted-foreground">{{
                            user.email
                        }}</TableCell>
                        <TableCell>
                            <NativeSelect
                                :model-value="user.roles[0]"
                                :disabled="pendingId === user.id"
                                @update:model-value="
                                    (value) => changeRole(user, value as string)
                                "
                            >
                                <option
                                    v-for="role in roleOptions"
                                    :key="role"
                                    :value="role"
                                >
                                    {{ role }}
                                </option>
                            </NativeSelect>
                        </TableCell>
                        <TableCell>
                            <Badge
                                :variant="
                                    user.deleted_at ? 'secondary' : 'success'
                                "
                            >
                                {{ user.deleted_at ? 'inactive' : 'active' }}
                            </Badge>
                        </TableCell>
                        <TableCell class="text-muted-foreground">{{
                            formatDate(user.created_at)
                        }}</TableCell>
                        <TableCell class="text-right">
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="
                                    pendingId === user.id || !!user.deleted_at
                                "
                                @click="deactivate(user)"
                            >
                                Deactivate
                            </Button>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </Card>

        <EmptyState
            v-else
            title="No users yet"
            description="Teachers and students will appear here once they join."
            :icon="UsersIcon"
        />

        <div class="mt-4">
            <Pagination :links="users.links" />
        </div>
    </AppShell>
</template>
