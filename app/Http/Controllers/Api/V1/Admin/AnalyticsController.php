<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Services\Analytics\AdminAnalyticsService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function overview(Request $request, AdminAnalyticsService $service): JsonResponse
    {
        abort_unless(
            $request->user()->hasRole(RoleName::SuperAdmin->value) && $request->user()->can(PermissionName::AnalyticsView->value),
            403
        );

        return ApiResponse::success($service->overview($request->user()->organization_id), 'Analytics overview loaded.');
    }
}
