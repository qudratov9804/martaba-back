import {
    Award,
    BookOpen,
    Building2,
    LayoutDashboard,
    ListTree,
    Percent,
    Receipt,
    ScrollText,
    Star,
    Users,
    Wallet,
} from 'lucide-vue-next';
import type { Component } from 'vue';

export interface NavItem {
    label: string;
    href: string;
    icon: Component;
    routeName: string;
}

export const adminNavigation: NavItem[] = [
    {
        label: 'Dashboard',
        href: '/admin/dashboard',
        icon: LayoutDashboard,
        routeName: 'admin.dashboard',
    },
    {
        label: 'Organizations',
        href: '/admin/organizations',
        icon: Building2,
        routeName: 'admin.organizations.index',
    },
    {
        label: 'Users',
        href: '/admin/users',
        icon: Users,
        routeName: 'admin.users.index',
    },
    {
        label: 'Categories',
        href: '/admin/categories',
        icon: ListTree,
        routeName: 'admin.categories.index',
    },
    {
        label: 'Courses',
        href: '/admin/courses',
        icon: BookOpen,
        routeName: 'admin.courses.index',
    },
    {
        label: 'Reviews',
        href: '/admin/reviews',
        icon: Star,
        routeName: 'admin.reviews.index',
    },
    {
        label: 'Coupons',
        href: '/admin/coupons',
        icon: Percent,
        routeName: 'admin.coupons.index',
    },
    {
        label: 'Orders',
        href: '/admin/orders',
        icon: Receipt,
        routeName: 'admin.orders.index',
    },
    {
        label: 'Payments',
        href: '/admin/payments',
        icon: Wallet,
        routeName: 'admin.payments.index',
    },
    {
        label: 'Certificates',
        href: '/admin/certificates',
        icon: Award,
        routeName: 'admin.certificates.index',
    },
    {
        label: 'Audit Logs',
        href: '/admin/audit-logs',
        icon: ScrollText,
        routeName: 'admin.audit-logs.index',
    },
];

export const teacherNavigation: NavItem[] = [
    {
        label: 'Dashboard',
        href: '/teacher/dashboard',
        icon: LayoutDashboard,
        routeName: 'teacher.dashboard',
    },
    {
        label: 'My Courses',
        href: '/teacher/courses',
        icon: BookOpen,
        routeName: 'teacher.courses.index',
    },
];
