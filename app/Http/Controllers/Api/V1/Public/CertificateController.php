<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Enums\CertificateStatus;
use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class CertificateController extends Controller
{
    public function verify(string $code): JsonResponse
    {
        $certificate = Certificate::where('verification_code', $code)->first();

        if (! $certificate) {
            return ApiResponse::success(['valid' => false], 'Certificate not found.');
        }

        return ApiResponse::success([
            'valid' => $certificate->status === CertificateStatus::Issued,
            'certificate_number' => $certificate->certificate_number,
            'student' => $certificate->student_name_snapshot,
            'course' => $certificate->course_name_snapshot,
            'organization' => $certificate->organization_name_snapshot,
            'issued_at' => $certificate->issued_at?->toDateString(),
            'status' => $certificate->status,
        ], 'Certificate verification result.');
    }
}
