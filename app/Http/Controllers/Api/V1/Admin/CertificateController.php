<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\PermissionName;
use App\Http\Controllers\Controller;
use App\Http\Resources\CertificateResource;
use App\Models\Certificate;
use App\Services\Certificates\CertificateService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->can(PermissionName::CertificatesView->value), 403);

        $certificates = Certificate::query()
            ->where('organization_id', $request->user()->organization_id)
            ->orderByDesc('issued_at')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(CertificateResource::collection($certificates), 'Certificates loaded.');
    }

    public function show(Request $request, Certificate $certificate): JsonResponse
    {
        abort_unless($request->user()->can(PermissionName::CertificatesView->value), 403);

        return ApiResponse::success(new CertificateResource($certificate), 'Certificate loaded.');
    }

    public function revoke(Certificate $certificate, CertificateService $service): JsonResponse
    {
        $this->authorize('revoke', $certificate);

        $certificate = $service->revoke($certificate);

        return ApiResponse::success(new CertificateResource($certificate), 'Certificate revoked.');
    }

    public function reissue(Certificate $certificate, CertificateService $service): JsonResponse
    {
        $this->authorize('reissue', $certificate);

        $certificate = $service->reissue($certificate);

        return ApiResponse::success(new CertificateResource($certificate), 'Certificate reissued.');
    }
}
