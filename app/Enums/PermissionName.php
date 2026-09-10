<?php

namespace App\Enums;

enum PermissionName: string
{
    case OrganizationsView = 'organizations.view';
    case OrganizationsCreate = 'organizations.create';
    case OrganizationsUpdate = 'organizations.update';
    case OrganizationsDelete = 'organizations.delete';

    case UsersView = 'users.view';
    case UsersCreate = 'users.create';
    case UsersUpdate = 'users.update';
    case UsersDelete = 'users.delete';
    case UsersAssignRoles = 'users.assign_roles';

    case CategoriesView = 'categories.view';
    case CategoriesCreate = 'categories.create';
    case CategoriesUpdate = 'categories.update';
    case CategoriesDelete = 'categories.delete';

    case CoursesView = 'courses.view';
    case CoursesCreate = 'courses.create';
    case CoursesUpdate = 'courses.update';
    case CoursesDelete = 'courses.delete';
    case CoursesPublish = 'courses.publish';
    case CoursesArchive = 'courses.archive';

    case LessonsManage = 'lessons.manage';
    case QuizzesManage = 'quizzes.manage';
    case AssignmentsManage = 'assignments.manage';

    case StudentsView = 'students.view';
    case StudentsManage = 'students.manage';

    case OrdersView = 'orders.view';
    case PaymentsView = 'payments.view';
    case PaymentsRefund = 'payments.refund';

    case CouponsView = 'coupons.view';
    case CouponsCreate = 'coupons.create';
    case CouponsUpdate = 'coupons.update';
    case CouponsDelete = 'coupons.delete';

    case CertificatesView = 'certificates.view';
    case CertificatesIssue = 'certificates.issue';
    case CertificatesRevoke = 'certificates.revoke';
    case CertificatesReissue = 'certificates.reissue';

    case AnalyticsView = 'analytics.view';
    case ReportsExport = 'reports.export';

    case SettingsManage = 'settings.manage';
    case AuditLogsView = 'audit_logs.view';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }
}
