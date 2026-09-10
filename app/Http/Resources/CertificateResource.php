<?php

namespace App\Http\Resources;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Certificate */
class CertificateResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'certificate_number' => $this->certificate_number,
            'verification_code' => $this->verification_code,
            'title' => $this->title,
            'student_name' => $this->student_name_snapshot,
            'course_name' => $this->course_name_snapshot,
            'teacher_name' => $this->teacher_name_snapshot,
            'organization_name' => $this->organization_name_snapshot,
            'issued_at' => $this->issued_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'status' => $this->status,
            'pdf_url' => $this->pdf_path ? Storage::disk('public')->url($this->pdf_path) : null,
            'qr_code_url' => $this->qr_code_path ? Storage::disk('public')->url($this->qr_code_path) : null,
            'verification_url' => url("/api/v1/certificates/verify/{$this->verification_code}"),
        ];
    }
}
