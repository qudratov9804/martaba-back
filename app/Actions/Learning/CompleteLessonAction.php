<?php

namespace App\Actions\Learning;

use App\Enums\LessonProgressStatus;
use App\Models\CourseLesson;
use App\Models\LessonProgress;
use App\Models\User;
use App\Services\Learning\CourseProgressService;

class CompleteLessonAction
{
    public function __construct(
        private readonly CourseProgressService $courseProgressService,
        private readonly EvaluateCourseCompletionAction $evaluateCourseCompletionAction,
    ) {}

    public function handle(User $student, CourseLesson $lesson): LessonProgress
    {
        $existing = LessonProgress::query()
            ->where('student_id', $student->id)
            ->where('lesson_id', $lesson->id)
            ->first();

        $progress = LessonProgress::updateOrCreate(
            ['student_id' => $student->id, 'lesson_id' => $lesson->id],
            [
                'course_id' => $lesson->course_id,
                'status' => LessonProgressStatus::Completed,
                'progress_percent' => 100,
                'started_at' => $existing?->started_at ?? now(),
                'completed_at' => now(),
            ]
        );

        $this->courseProgressService->recalculate($student, $lesson->course);
        $this->evaluateCourseCompletionAction->handle($student, $lesson->course);

        return $progress->fresh();
    }
}
