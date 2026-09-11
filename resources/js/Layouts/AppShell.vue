<script setup lang="ts">
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import FlashMessage from '@/Components/dashboard/FlashMessage.vue';
import { useAuth } from '@/composables/useAuth';
import {
    adminNavigation,
    teacherNavigation,
    type NavItem,
} from '@/config/navigation';
import { Link } from '@inertiajs/vue3';
import { ChevronDown, GraduationCap } from 'lucide-vue-next';
import { computed } from 'vue';

const { user, isSuperAdmin } = useAuth();

const navigation = computed<NavItem[]>(() =>
    isSuperAdmin.value ? adminNavigation : teacherNavigation,
);

const portalLabel = computed(() =>
    isSuperAdmin.value ? 'Admin Panel' : 'Teacher Panel',
);

function initials(name: string): string {
    return name
        .split(' ')
        .map((part) => part.charAt(0))
        .slice(0, 2)
        .join('')
        .toUpperCase();
}
</script>

<template>
    <div class="bg-background text-foreground flex min-h-screen">
        <!-- Sidebar -->
        <aside
            class="border-border bg-card hidden w-64 shrink-0 flex-col border-r lg:flex"
        >
            <div
                class="border-border flex h-16 items-center gap-2 border-b px-6"
            >
                <GraduationCap class="text-primary h-6 w-6" />
                <div class="flex flex-col leading-none">
                    <span class="text-sm font-semibold">Martaba</span>
                    <span class="text-muted-foreground text-xs">{{
                        portalLabel
                    }}</span>
                </div>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                <Link
                    v-for="item in navigation"
                    :key="item.routeName"
                    :href="item.href"
                    :class="[
                        'flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors',
                        route().current(item.routeName + '*')
                            ? 'bg-primary text-primary-foreground'
                            : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground',
                    ]"
                >
                    <component :is="item.icon" class="h-4 w-4" />
                    {{ item.label }}
                </Link>
            </nav>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <!-- Topbar -->
            <header
                class="border-border bg-card flex h-16 items-center justify-between border-b px-4 sm:px-6"
            >
                <div class="min-w-0">
                    <slot name="header" />
                </div>

                <Dropdown v-if="user" align="right" width="48">
                    <template #trigger>
                        <button
                            type="button"
                            class="hover:bg-accent flex items-center gap-2 rounded-md px-2 py-1.5 text-sm"
                        >
                            <span
                                class="bg-primary text-primary-foreground flex h-8 w-8 items-center justify-center rounded-full text-xs font-semibold"
                            >
                                {{ initials(user.name) }}
                            </span>
                            <span
                                class="hidden text-sm font-medium sm:inline"
                                >{{ user.name }}</span
                            >
                            <ChevronDown
                                class="text-muted-foreground h-4 w-4"
                            />
                        </button>
                    </template>

                    <template #content>
                        <DropdownLink :href="route('profile.edit')"
                            >Profile</DropdownLink
                        >
                        <DropdownLink
                            :href="route('logout')"
                            method="post"
                            as="button"
                            >Log Out</DropdownLink
                        >
                    </template>
                </Dropdown>
            </header>

            <main class="flex-1 overflow-y-auto p-4 sm:p-6">
                <FlashMessage />
                <slot />
            </main>
        </div>
    </div>
</template>
