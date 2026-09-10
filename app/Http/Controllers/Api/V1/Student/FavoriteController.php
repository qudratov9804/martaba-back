<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteResource;
use App\Models\Course;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $favorites = $request->user()->favorites()
            ->with('course')
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(FavoriteResource::collection($favorites), 'Favorites loaded.');
    }

    public function store(Request $request, Course $course): JsonResponse
    {
        $favorite = $request->user()->favorites()->firstOrCreate(
            ['course_id' => $course->id],
            ['created_at' => now()],
        );

        return ApiResponse::success(new FavoriteResource($favorite->load('course')), 'Added to favorites.', status: 201);
    }

    public function destroy(Request $request, Course $course): JsonResponse
    {
        $request->user()->favorites()->where('course_id', $course->id)->delete();

        return ApiResponse::success(null, 'Removed from favorites.');
    }
}
