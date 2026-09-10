<?php

namespace App\Services\Analytics;

use App\Enums\CertificateStatus;
use App\Enums\CourseStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\OrderStatus;
use App\Enums\RefundStatus;
use App\Enums\RoleName;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Refund;
use App\Models\User;

class AdminAnalyticsService
{
    /**
     * @return array<string, mixed>
     */
    public function overview(int $organizationId): array
    {
        $totalUsers = User::where('organization_id', $organizationId)->count();
        $totalTeachers = User::where('organization_id', $organizationId)->role(RoleName::Teacher->value)->count();
        $totalStudents = User::where('organization_id', $organizationId)->role(RoleName::Student->value)->count();

        $totalCourses = Course::where('organization_id', $organizationId)->count();
        $publishedCourses = Course::where('organization_id', $organizationId)->where('status', CourseStatus::Published)->count();

        $totalEnrollments = Enrollment::where('organization_id', $organizationId)->count();
        $completedEnrollments = Enrollment::where('organization_id', $organizationId)->where('status', EnrollmentStatus::Completed)->count();

        $paidOrdersCount = Order::where('organization_id', $organizationId)->where('status', OrderStatus::Paid)->count();
        $totalRevenueMinor = (int) Order::where('organization_id', $organizationId)->where('status', OrderStatus::Paid)->sum('total_minor');

        $totalRefundsMinor = (int) Refund::whereHas('order', fn ($query) => $query->where('organization_id', $organizationId))
            ->where('status', RefundStatus::Succeeded)
            ->sum('amount_minor');

        $totalCertificatesIssued = Certificate::where('organization_id', $organizationId)->where('status', CertificateStatus::Issued)->count();

        $activeStudents30d = Enrollment::where('organization_id', $organizationId)
            ->where('updated_at', '>=', now()->subDays(30))
            ->distinct()
            ->count('student_id');

        return [
            'total_users' => $totalUsers,
            'total_teachers' => $totalTeachers,
            'total_students' => $totalStudents,
            'total_courses' => $totalCourses,
            'published_courses' => $publishedCourses,
            'total_enrollments' => $totalEnrollments,
            'completion_rate' => $totalEnrollments > 0 ? round(($completedEnrollments / $totalEnrollments) * 100, 2) : 0.0,
            'paid_orders_count' => $paidOrdersCount,
            'total_revenue_minor' => $totalRevenueMinor,
            'total_refunds_minor' => $totalRefundsMinor,
            'total_certificates_issued' => $totalCertificatesIssued,
            'active_students_30d' => $activeStudents30d,
        ];
    }
}
