<?php

namespace App\Services\Analytics;

use App\Enums\CourseStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\OrderStatus;
use App\Models\Course;
use App\Models\CourseProgress;
use App\Models\Enrollment;
use App\Models\OrderItem;
use App\Models\User;

class TeacherAnalyticsService
{
    /**
     * @return list<int>
     */
    private function courseIds(User $teacher): array
    {
        return Course::query()
            ->where(function ($query) use ($teacher) {
                $query->where('created_by', $teacher->id)
                    ->orWhereHas('courseInstructors', fn ($q) => $q->where('user_id', $teacher->id));
            })
            ->pluck('id')
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function overview(User $teacher): array
    {
        $courseIds = $this->courseIds($teacher);

        $totalCourses = count($courseIds);
        $publishedCourses = Course::whereIn('id', $courseIds)->where('status', CourseStatus::Published)->count();

        $enrollments = Enrollment::whereIn('course_id', $courseIds);
        $totalStudents = (clone $enrollments)->distinct()->count('student_id');
        $totalEnrollments = (clone $enrollments)->count();
        $completedEnrollments = (clone $enrollments)->where('status', EnrollmentStatus::Completed)->count();
        $newEnrollments30d = (clone $enrollments)->where('enrolled_at', '>=', now()->subDays(30))->count();

        $totalRevenueMinor = $this->revenueMinor($courseIds);

        return [
            'total_courses' => $totalCourses,
            'published_courses' => $publishedCourses,
            'total_students' => $totalStudents,
            'total_enrollments' => $totalEnrollments,
            'new_enrollments_30d' => $newEnrollments30d,
            'completion_rate' => $totalEnrollments > 0 ? round(($completedEnrollments / $totalEnrollments) * 100, 2) : 0.0,
            'total_revenue_minor' => $totalRevenueMinor,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function revenueByCourse(User $teacher): array
    {
        $courseIds = $this->courseIds($teacher);

        return Course::query()
            ->whereIn('id', $courseIds)
            ->get()
            ->map(fn (Course $course) => [
                'course_id' => $course->id,
                'title' => $course->title,
                'revenue_minor' => $this->revenueMinor([$course->id]),
                'currency' => $course->currency,
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function courseOverview(Course $course): array
    {
        $enrollments = Enrollment::where('course_id', $course->id);
        $total = (clone $enrollments)->count();
        $completed = (clone $enrollments)->where('status', EnrollmentStatus::Completed)->count();

        $avgProgress = CourseProgress::where('course_id', $course->id)->avg('progress_percent');

        return [
            'course_id' => $course->id,
            'total_enrollments' => $total,
            'completed_enrollments' => $completed,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0.0,
            'average_progress_percent' => $avgProgress !== null ? round((float) $avgProgress, 2) : 0.0,
            'revenue_minor' => $this->revenueMinor([$course->id]),
        ];
    }

    /**
     * @param  list<int>  $courseIds
     */
    private function revenueMinor(array $courseIds): int
    {
        if (empty($courseIds)) {
            return 0;
        }

        return (int) OrderItem::query()
            ->whereIn('course_id', $courseIds)
            ->whereHas('order', fn ($query) => $query->where('status', OrderStatus::Paid))
            ->sum('total_minor');
    }
}
