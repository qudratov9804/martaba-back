<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Review::class);

        $reviews = Review::query()
            ->whereHas('course', fn ($query) => $query->where('organization_id', $request->user()->organization_id))
            ->with(['course', 'student'])
            ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Reviews/Index', [
            'reviews' => ReviewResource::collection($reviews),
            'filters' => $request->only('status'),
        ]);
    }
}
