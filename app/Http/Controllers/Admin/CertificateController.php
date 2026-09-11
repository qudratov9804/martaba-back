<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PermissionName;
use App\Http\Controllers\Controller;
use App\Http\Resources\CertificateResource;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CertificateController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can(PermissionName::CertificatesView->value), 403);

        $certificates = Certificate::query()
            ->where('organization_id', $request->user()->organization_id)
            ->orderByDesc('issued_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Certificates/Index', [
            'certificates' => CertificateResource::collection($certificates),
        ]);
    }
}
