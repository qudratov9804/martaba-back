<?php

namespace App\Http\Resources;

use App\Models\AssignmentSubmission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AssignmentSubmission */
class AssignmentSubmissionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'assignment_id' => $this->assignment_id,
            'student_id' => $this->student_id,
            'status' => $this->status,
            'text_submission' => $this->text_submission,
            'media' => new MediaResource($this->whenLoaded('media')),
            'score' => $this->score,
            'feedback' => $this->feedback,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'graded_at' => $this->graded_at?->toIso8601String(),
        ];
    }
}
