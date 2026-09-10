<?php

namespace App\Actions\Assignments;

use App\Enums\AssignmentSubmissionStatus;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class SubmitAssignmentAction
{
    /**
     * @param  array{text_submission?: string, media_id?: int}  $data
     */
    public function handle(User $student, Assignment $assignment, array $data): AssignmentSubmission
    {
        $isLate = $assignment->isPastDeadline();

        if ($isLate && ! $assignment->allow_late_submission) {
            throw ValidationException::withMessages([
                'assignment' => ['The deadline for this assignment has passed.'],
            ]);
        }

        return AssignmentSubmission::updateOrCreate(
            ['assignment_id' => $assignment->id, 'student_id' => $student->id],
            [
                'status' => $isLate ? AssignmentSubmissionStatus::Late : AssignmentSubmissionStatus::Submitted,
                'text_submission' => $data['text_submission'] ?? null,
                'media_id' => $data['media_id'] ?? null,
                'submitted_at' => now(),
                'score' => null,
                'graded_at' => null,
                'graded_by' => null,
            ],
        );
    }
}
