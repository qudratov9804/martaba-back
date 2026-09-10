<?php

namespace App\Actions\Learning;

use App\Enums\LessonProgressStatus;
use App\Models\CourseLesson;
use App\Models\LessonProgress;
use App\Models\User;

class StartLessonAction
{
    public function handle(User $student, CourseLesson $lesson): LessonProgress
    {
        $progress = LessonProgress::firstOrNew([
            'student_id' => $student->id,
            'lesson_id' => $lesson->id,
        ]);

        if (! $progress->exists) {
            $progress->course_id = $lesson->course_id;
            $progress->status = LessonProgressStatus::InProgress;
            $progress->started_at = now();
            $progress->save();
        } elseif ($progress->status === LessonProgressStatus::NotStarted) {
            $progress->update([
                'status' => LessonProgressStatus::InProgress,
                'started_at' => now(),
            ]);
        }

        return $progress;
    }
}
