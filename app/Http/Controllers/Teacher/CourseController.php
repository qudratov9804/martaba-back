<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Course::class);

        $courses = Course::query()
            ->where(function ($query) use ($request) {
                $query->where('created_by', $request->user()->id)
                    ->orWhereHas('courseInstructors', fn ($q) => $q->where('user_id', $request->user()->id));
            })
            ->withCount('lessons')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Teacher/Courses/Index', [
            'courses' => $courses,
        ]);
    }
}
