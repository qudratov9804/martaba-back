<?php

namespace App\Actions\Learning;

use App\Enums\LessonProgressStatus;
use App\Models\CourseLesson;
use App\Models\LessonProgress;
use App\Models\User;

class UpdateLessonProgressAction
{
    /**
     * @param  array{watch_seconds?: int, last_position_seconds?: int, progress_percent?: int}  $data
     */
    public function handle(User $student, CourseLesson $lesson, array $data): LessonProgress
    {
        $progress = LessonProgress::firstOrCreate(
            ['student_id' => $student->id, 'lesson_id' => $lesson->id],
            ['course_id' => $lesson->course_id, 'status' => LessonProgressStatus::InProgress, 'started_at' => now()],
        );

        $progress->update([
            'watch_seconds' => $data['watch_seconds'] ?? $progress->watch_seconds,
            'last_position_seconds' => $data['last_position_seconds'] ?? $progress->last_position_seconds,
            'progress_percent' => min(100, $data['progress_percent'] ?? $progress->progress_percent),
            'status' => $progress->status === LessonProgressStatus::NotStarted
                ? LessonProgressStatus::InProgress
                : $progress->status,
        ]);

        return $progress;
    }
}
