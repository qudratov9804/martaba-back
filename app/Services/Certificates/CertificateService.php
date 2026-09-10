<?php

namespace App\Services\Certificates;

use App\Enums\CertificateStatus;
use App\Models\Certificate;
use App\Models\Enrollment;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateService
{
    /**
     * Issue a certificate for a completed enrollment. Idempotent: if an
     * issued certificate already exists for this enrollment, it is returned
     * unchanged rather than generating a duplicate.
     */
    public function issue(Enrollment $enrollment): ?Certificate
    {
        $course = $enrollment->course;

        if (! $course->certificate_enabled) {
            return null;
        }

        $existing = Certificate::query()
            ->where('enrollment_id', $enrollment->id)
            ->where('status', CertificateStatus::Issued)
            ->first();

        if ($existing) {
            return $existing;
        }

        $student = $enrollment->student;
        $organization = $course->organization;
        $primaryInstructor = $course->courseInstructors()
            ->where('is_primary', true)
            ->with('user')
            ->first()?->user;

        $certificate = Certificate::create([
            'organization_id' => $organization->id,
            'course_id' => $course->id,
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'certificate_number' => $this->generateCertificateNumber(),
            'verification_code' => $this->generateVerificationCode(),
            'title' => 'Certificate of Completion',
            'student_name_snapshot' => $student->name,
            'course_name_snapshot' => $course->title,
            'teacher_name_snapshot' => $primaryInstructor?->name,
            'organization_name_snapshot' => $organization->name,
            'issued_at' => now(),
            'status' => CertificateStatus::Issued,
        ]);

        $this->renderFiles($certificate);

        return $certificate;
    }

    public function revoke(Certificate $certificate): Certificate
    {
        $certificate->update(['status' => CertificateStatus::Revoked]);

        return $certificate;
    }

    public function reissue(Certificate $certificate): Certificate
    {
        $this->revoke($certificate);

        return $this->issue($certificate->enrollment)
            ?? throw new \RuntimeException('Cannot reissue a certificate for a course with certificates disabled.');
    }

    private function renderFiles(Certificate $certificate): void
    {
        $verificationUrl = url("/api/v1/certificates/verify/{$certificate->verification_code}");

        $qrResult = (new PngWriter)->write(new QrCode($verificationUrl, size: 300, margin: 10));

        $qrPath = "certificates/{$certificate->organization_id}/{$certificate->certificate_number}-qr.png";
        Storage::disk('public')->put($qrPath, $qrResult->getString());

        $pdf = Pdf::loadView('certificates.pdf', [
            'certificate' => $certificate,
            'qrDataUri' => $qrResult->getDataUri(),
            'verificationUrl' => $verificationUrl,
        ])->setPaper('a4', 'landscape');

        $pdfPath = "certificates/{$certificate->organization_id}/{$certificate->certificate_number}.pdf";
        Storage::disk('public')->put($pdfPath, $pdf->output());

        $certificate->update(['qr_code_path' => $qrPath, 'pdf_path' => $pdfPath]);
    }

    private function generateCertificateNumber(): string
    {
        return 'CERT-'.now()->format('Y').'-'.Str::padLeft((string) (Certificate::count() + 1), 8, '0');
    }

    private function generateVerificationCode(): string
    {
        do {
            $code = Str::upper(Str::random(16));
        } while (Certificate::where('verification_code', $code)->exists());

        return $code;
    }
}
