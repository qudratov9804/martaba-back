<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Organizations\CreateOrganizationAction;
use App\Actions\Organizations\UpdateOrganizationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOrganizationRequest;
use App\Http\Requests\Admin\UpdateOrganizationRequest;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Organization::class);

        $organizations = Organization::query()
            ->when($request->string('search')->toString(), fn ($query, $search) => $query->where('name', 'like', "%{$search}%")
            )
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(OrganizationResource::collection($organizations), 'Organizations loaded.');
    }

    public function store(StoreOrganizationRequest $request, CreateOrganizationAction $action): JsonResponse
    {
        $this->authorize('create', Organization::class);

        $organization = $action->handle($request->validated());

        return ApiResponse::success(new OrganizationResource($organization), 'Organization created.', status: 201);
    }

    public function show(Organization $organization): JsonResponse
    {
        $this->authorize('view', $organization);

        return ApiResponse::success(new OrganizationResource($organization), 'Organization loaded.');
    }

    public function update(UpdateOrganizationRequest $request, Organization $organization, UpdateOrganizationAction $action): JsonResponse
    {
        $this->authorize('update', $organization);

        $organization = $action->handle($organization, $request->validated());

        return ApiResponse::success(new OrganizationResource($organization), 'Organization updated.');
    }

    public function destroy(Organization $organization): JsonResponse
    {
        $this->authorize('delete', $organization);

        $organization->delete();

        return ApiResponse::success(null, 'Organization deleted.');
    }
}
