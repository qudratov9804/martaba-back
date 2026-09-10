<?php

namespace App\Actions\Assignments;

use App\Actions\Learning\EvaluateCourseCompletionAction;
use App\Enums\AssignmentSubmissionStatus;
use App\Models\AssignmentSubmission;
use App\Models\User;

class GradeAssignmentAction
{
    public function __construct(private readonly EvaluateCourseCompletionAction $evaluateCourseCompletionAction) {}

    public function handle(AssignmentSubmission $submission, User $grader, int $score, ?string $feedback = null): AssignmentSubmission
    {
        $submission->update([
            'status' => AssignmentSubmissionStatus::Graded,
            'score' => $score,
            'feedback' => $feedback,
            'graded_at' => now(),
            'graded_by' => $grader->id,
        ]);

        $this->evaluateCourseCompletionAction->handle($submission->student, $submission->assignment->course);

        return $submission->fresh();
    }
}
