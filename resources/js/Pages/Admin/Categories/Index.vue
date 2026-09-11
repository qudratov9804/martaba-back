<script setup lang="ts">
import EmptyState from '@/Components/dashboard/EmptyState.vue';
import PageHeader from '@/Components/dashboard/PageHeader.vue';
import { Badge } from '@/Components/ui/badge';
import { Card, CardContent } from '@/Components/ui/card';
import AppShell from '@/Layouts/AppShell.vue';
import { Head } from '@inertiajs/vue3';
import { ListTree } from 'lucide-vue-next';

interface Category {
    id: number;
    name: string;
    slug: string;
    status: string;
    children: Category[];
}

defineProps<{ categories: Category[] }>();
</script>

<template>
    <Head title="Categories" />

    <AppShell>
        <template #header>
            <h2 class="truncate text-lg font-semibold">Categories</h2>
        </template>

        <PageHeader
            title="Categories"
            description="The course category tree for your organization."
        />

        <Card v-if="categories.length">
            <CardContent class="divide-border divide-y p-0">
                <div
                    v-for="category in categories"
                    :key="category.id"
                    class="p-4"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-foreground font-medium">{{
                                category.name
                            }}</span>
                            <span class="text-muted-foreground text-sm"
                                >/{{ category.slug }}</span
                            >
                        </div>
                        <Badge
                            :variant="
                                category.status === 'active'
                                    ? 'success'
                                    : 'secondary'
                            "
                        >
                            {{ category.status }}
                        </Badge>
                    </div>

                    <div
                        v-if="category.children.length"
                        class="border-border mt-3 ml-4 space-y-2 border-l pl-4"
                    >
                        <div
                            v-for="child in category.children"
                            :key="child.id"
                            class="flex items-center justify-between text-sm"
                        >
                            <span class="text-foreground">{{
                                child.name
                            }}</span>
                            <Badge
                                :variant="
                                    child.status === 'active'
                                        ? 'success'
                                        : 'secondary'
                                "
                            >
                                {{ child.status }}
                            </Badge>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <EmptyState
            v-else
            title="No categories yet"
            description="Create your first category to start organizing courses."
            :icon="ListTree"
        />
    </AppShell>
</template>
