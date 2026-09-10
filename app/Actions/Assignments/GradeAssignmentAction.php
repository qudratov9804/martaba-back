<?php

namespace App\Actions\Assignments;

use App\Enums\AssignmentSubmissionStatus;
use App\Models\AssignmentSubmission;
use App\Models\User;

class GradeAssignmentAction
{
    public function handle(AssignmentSubmission $submission, User $grader, int $score, ?string $feedback = null): AssignmentSubmission
    {
        $submission->update([
            'status' => AssignmentSubmissionStatus::Graded,
            'score' => $score,
            'feedback' => $feedback,
            'graded_at' => now(),
            'graded_by' => $grader->id,
        ]);

        return $submission->fresh();
    }
}
