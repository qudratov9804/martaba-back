<?php

namespace App\Services\Learning;

use App\Enums\LessonProgressStatus;
use App\Models\Course;
use App\Models\CourseProgress;
use App\Models\LessonProgress;
use App\Models\User;

class CourseProgressService
{
    public function recalculate(User $student, Course $course): CourseProgress
    {
        $totalLessons = $course->lessons()->count();
        $requiredLessonsTotal = $course->lessons()->where('is_required', true)->count();

        $completedLessonIds = LessonProgress::query()
            ->where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->where('status', LessonProgressStatus::Completed)
            ->pluck('lesson_id');

        $completedLessons = $completedLessonIds->count();

        $requiredLessonsCompleted = $totalLessons === 0 ? 0 : $course->lessons()
            ->where('is_required', true)
            ->whereIn('id', $completedLessonIds)
            ->count();

        $progressPercent = $totalLessons > 0
            ? (int) round(($completedLessons / $totalLessons) * 100)
            : 0;

        $lastLessonId = LessonProgress::query()
            ->where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->orderByDesc('updated_at')
            ->value('lesson_id');

        $existing = CourseProgress::query()
            ->where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->first();

        $progress = CourseProgress::updateOrCreate(
            ['student_id' => $student->id, 'course_id' => $course->id],
            [
                'progress_percent' => $progressPercent,
                'completed_lessons' => $completedLessons,
                'total_lessons' => $totalLessons,
                'required_lessons_completed' => $requiredLessonsCompleted,
                'required_lessons_total' => $requiredLessonsTotal,
                'last_lesson_id' => $lastLessonId,
                'started_at' => $existing?->started_at ?? now(),
            ]
        );

        $allLessonsDone = $requiredLessonsTotal > 0 && $requiredLessonsCompleted >= $requiredLessonsTotal;

        if ($allLessonsDone && ! $progress->completed_at) {
            $progress->update(['completed_at' => now()]);
        }

        return $progress;
    }
}
