<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\PermissionName;
use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->can(PermissionName::AuditLogsView->value), 403);

        $logs = AuditLog::query()
            ->where('organization_id', $request->user()->organization_id)
            ->with('user')
            ->when($request->string('action')->toString(), fn ($query, $action) => $query->where('action', $action))
            ->when($request->string('subject_type')->toString(), fn ($query, $type) => $query->where('subject_type', $type))
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(AuditLogResource::collection($logs), 'Audit logs loaded.');
    }
}
