<?php

namespace App\Http\Controllers\Api\V1\Media;

use App\Actions\Media\UploadMediaAction;
use App\Enums\MediaVisibility;
use App\Http\Controllers\Controller;
use App\Http\Requests\Media\StoreMediaRequest;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $media = Media::query()
            ->where('uploaded_by', $request->user()->id)
            ->when($request->string('mime_type')->toString(), fn ($query, $mime) => $query->where('mime_type', 'like', "{$mime}%"))
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 24));

        return ApiResponse::success(MediaResource::collection($media), 'Media loaded.');
    }

    public function store(StoreMediaRequest $request, UploadMediaAction $action): JsonResponse
    {
        $this->authorize('create', Media::class);

        $visibility = MediaVisibility::from($request->string('visibility')->toString() ?: MediaVisibility::Private->value);

        $media = $action->handle($request->user(), $request->file('file'), $visibility);

        return ApiResponse::success(new MediaResource($media), 'File uploaded.', status: 201);
    }

    public function show(Media $media): JsonResponse
    {
        $this->authorize('view', $media);

        return ApiResponse::success(new MediaResource($media), 'Media loaded.');
    }

    public function destroy(Media $media): JsonResponse
    {
        $this->authorize('delete', $media);

        Storage::disk($media->disk)->delete($media->path);
        $media->delete();

        return ApiResponse::success(null, 'Media deleted.');
    }
}
