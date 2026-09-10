<?php

namespace App\Jobs;

use App\Events\CertificateIssued;
use App\Models\Enrollment;
use App\Services\Certificates\CertificateService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateCertificateJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Enrollment $enrollment) {}

    public function handle(CertificateService $certificateService): void
    {
        $certificate = $certificateService->issue($this->enrollment);

        if ($certificate) {
            CertificateIssued::dispatch($certificate);
        }
    }
}
