<script setup lang="ts">
import PageHeader from '@/Components/dashboard/PageHeader.vue';
import StatCard from '@/Components/dashboard/StatCard.vue';
import AppShell from '@/Layouts/AppShell.vue';
import { formatMoney } from '@/lib/format';
import { Head } from '@inertiajs/vue3';
import {
    Award,
    BookOpen,
    GraduationCap,
    RefreshCcw,
    Users,
    Wallet,
} from 'lucide-vue-next';

interface Overview {
    total_users: number;
    total_teachers: number;
    total_students: number;
    total_courses: number;
    published_courses: number;
    total_enrollments: number;
    completion_rate: number;
    paid_orders_count: number;
    total_revenue_minor: number;
    total_refunds_minor: number;
    total_certificates_issued: number;
    active_students_30d: number;
}

defineProps<{ overview: Overview }>();
</script>

<template>
    <Head title="Admin Dashboard" />

    <AppShell>
        <template #header>
            <h2 class="truncate text-lg font-semibold">Admin Dashboard</h2>
        </template>

        <PageHeader
            title="Overview"
            description="Platform-wide activity for your organization."
        />

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard
                title="Total Users"
                :value="overview.total_users"
                :icon="Users"
            />
            <StatCard
                title="Teachers"
                :value="overview.total_teachers"
                :icon="GraduationCap"
            />
            <StatCard
                title="Students"
                :value="overview.total_students"
                :icon="Users"
            />
            <StatCard
                title="Active Students (30d)"
                :value="overview.active_students_30d"
                :icon="Users"
            />

            <StatCard
                title="Total Courses"
                :value="overview.total_courses"
                :icon="BookOpen"
            />
            <StatCard
                title="Published Courses"
                :value="overview.published_courses"
                :icon="BookOpen"
            />
            <StatCard
                title="Total Enrollments"
                :value="overview.total_enrollments"
                :icon="BookOpen"
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
                :hint="`${overview.paid_orders_count} paid orders`"
            />
            <StatCard
                title="Refunds"
                :value="formatMoney(overview.total_refunds_minor)"
                :icon="RefreshCcw"
            />
            <StatCard
                title="Certificates Issued"
                :value="overview.total_certificates_issued"
                :icon="Award"
            />
        </div>
    </AppShell>
</template>
