<?php

namespace App\Listeners;

use App\Events\CourseCompleted;
use App\Jobs\GenerateCertificateJob;

class DispatchCertificateGenerationJob
{
    public function handle(CourseCompleted $event): void
    {
        GenerateCertificateJob::dispatch($event->enrollment);
    }
}
