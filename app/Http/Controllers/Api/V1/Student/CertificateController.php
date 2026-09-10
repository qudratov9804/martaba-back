<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\CertificateResource;
use App\Models\Certificate;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $certificates = $request->user()->certificates()
            ->orderByDesc('issued_at')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(CertificateResource::collection($certificates), 'Certificates loaded.');
    }

    public function show(Certificate $certificate): JsonResponse
    {
        $this->authorize('view', $certificate);

        return ApiResponse::success(new CertificateResource($certificate), 'Certificate loaded.');
    }

    public function download(Certificate $certificate)
    {
        $this->authorize('view', $certificate);

        abort_unless($certificate->pdf_path, 404);

        return Storage::disk('public')->download($certificate->pdf_path, "{$certificate->certificate_number}.pdf");
    }
}
