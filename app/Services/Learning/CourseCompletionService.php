<?php

namespace App\Services\Learning;

use App\Enums\AssignmentSubmissionStatus;
use App\Enums\LessonProgressStatus;
use App\Enums\LessonType;
use App\Enums\QuizAttemptStatus;
use App\Enums\QuizType;
use App\Models\Course;
use App\Models\LessonProgress;
use App\Models\QuizAttempt;
use App\Models\User;

/**
 * Evaluates a course's completion_rules_json against a student's actual
 * progress. Never infers completion from progress_percent alone (see spec
 * section 19) - each configured rule must independently pass.
 */
class CourseCompletionService
{
    public function isComplete(User $student, Course $course): bool
    {
        $rules = $course->completion_rules_json ?? [];

        $requireAllRequiredLessons = $rules['require_all_required_lessons'] ?? true;
        $minimumVideoWatchPercent = $rules['minimum_video_watch_percent'] ?? null;
        $minimumFinalQuizScore = $rules['minimum_final_quiz_score'] ?? null;
        $requireAllAssignments = $rules['require_all_assignments'] ?? false;

        if ($requireAllRequiredLessons && ! $this->allRequiredLessonsCompleted($student, $course)) {
            return false;
        }

        if ($minimumVideoWatchPercent !== null && ! $this->meetsVideoWatchThreshold($student, $course, (int) $minimumVideoWatchPercent)) {
            return false;
        }

        if ($minimumFinalQuizScore !== null && ! $this->meetsFinalQuizScore($student, $course, (float) $minimumFinalQuizScore)) {
            return false;
        }

        if ($requireAllAssignments && ! $this->allRequiredAssignmentsPassed($student, $course)) {
            return false;
        }

        return true;
    }

    private function allRequiredLessonsCompleted(User $student, Course $course): bool
    {
        $requiredLessonIds = $course->lessons()->where('is_required', true)->pluck('id');

        if ($requiredLessonIds->isEmpty()) {
            return true;
        }

        $completedCount = LessonProgress::query()
            ->where('student_id', $student->id)
            ->whereIn('lesson_id', $requiredLessonIds)
            ->where('status', LessonProgressStatus::Completed)
            ->count();

        return $completedCount >= $requiredLessonIds->count();
    }

    private function meetsVideoWatchThreshold(User $student, Course $course, int $threshold): bool
    {
        $requiredVideoLessonIds = $course->lessons()
            ->where('is_required', true)
            ->where('lesson_type', LessonType::Video)
            ->pluck('id');

        if ($requiredVideoLessonIds->isEmpty()) {
            return true;
        }

        $satisfiedCount = LessonProgress::query()
            ->where('student_id', $student->id)
            ->whereIn('lesson_id', $requiredVideoLessonIds)
            ->where('progress_percent', '>=', $threshold)
            ->count();

        return $satisfiedCount >= $requiredVideoLessonIds->count();
    }

    private function meetsFinalQuizScore(User $student, Course $course, float $threshold): bool
    {
        $finalQuizIds = $course->quizzes()->where('type', QuizType::Final)->pluck('id');

        if ($finalQuizIds->isEmpty()) {
            return true;
        }

        foreach ($finalQuizIds as $quizId) {
            $best = QuizAttempt::query()
                ->where('quiz_id', $quizId)
                ->where('student_id', $student->id)
                ->where('status', QuizAttemptStatus::Graded)
                ->max('percentage');

            if ($best === null || $best < $threshold) {
                return false;
            }
        }

        return true;
    }

    private function allRequiredAssignmentsPassed(User $student, Course $course): bool
    {
        $requiredAssignments = $course->assignments()->where('is_required', true)->get();

        if ($requiredAssignments->isEmpty()) {
            return true;
        }

        foreach ($requiredAssignments as $assignment) {
            $submission = $assignment->submissions()
                ->where('student_id', $student->id)
                ->where('status', AssignmentSubmissionStatus::Graded)
                ->first();

            if (! $submission || $submission->score < $assignment->passing_score) {
                return false;
            }
        }

        return true;
    }
}
