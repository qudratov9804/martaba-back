import type { PageProps } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useAuth() {
    const page = usePage<PageProps>();

    const user = computed(() => page.props.auth.user);

    function hasRole(role: string): boolean {
        return user.value?.roles.includes(role) ?? false;
    }

    function can(permission: string): boolean {
        return user.value?.permissions.includes(permission) ?? false;
    }

    const isSuperAdmin = computed(() => hasRole('super_admin'));
    const isTeacher = computed(() => hasRole('teacher'));
    const isStudent = computed(() => hasRole('student'));

    return { user, hasRole, can, isSuperAdmin, isTeacher, isStudent };
}
