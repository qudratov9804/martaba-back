<script setup lang="ts">
import PageHeader from '@/Components/dashboard/PageHeader.vue';
import StatCard from '@/Components/dashboard/StatCard.vue';
import AppShell from '@/Layouts/AppShell.vue';
import { formatMoney } from '@/lib/format';
import { Head } from '@inertiajs/vue3';
import {
    BookOpen,
    GraduationCap,
    UserPlus,
    Users,
    Wallet,
} from 'lucide-vue-next';

interface Overview {
    total_courses: number;
    published_courses: number;
    total_students: number;
    total_enrollments: number;
    new_enrollments_30d: number;
    completion_rate: number;
    total_revenue_minor: number;
}

defineProps<{ overview: Overview }>();
</script>

<template>
    <Head title="Teacher Dashboard" />

    <AppShell>
        <template #header>
            <h2 class="truncate text-lg font-semibold">Teacher Dashboard</h2>
        </template>

        <PageHeader
            title="Overview"
            description="How your courses are performing."
        />

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <StatCard
                title="My Courses"
                :value="overview.total_courses"
                :icon="BookOpen"
            />
            <StatCard
                title="Published Courses"
                :value="overview.published_courses"
                :icon="BookOpen"
            />
            <StatCard
                title="Total Students"
                :value="overview.total_students"
                :icon="Users"
            />

            <StatCard
                title="New Enrollments (30d)"
                :value="overview.new_enrollments_30d"
                :icon="UserPlus"
            />
            <StatCard
                title="Completion Rate"
                :value="`${overview.completion_rate}%`"
                :icon="GraduationCap"
            />
            <StatCard
                title="Revenue"
                :value="formatMoney(overview.total_revenue_minor)"
                :icon="Wallet"
            />
        </div>
    </AppShell>
</template>
