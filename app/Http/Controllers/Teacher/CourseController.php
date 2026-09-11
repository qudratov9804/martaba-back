<?php

namespace App\Http\Controllers\Teacher;

use App\Actions\Courses\CreateCourseAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreCourseRequest;
use App\Models\Category;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
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

    public function create(Request $request): Response
    {
        $this->authorize('create', Course::class);

        $categories = Category::query()
            ->where('organization_id', $request->user()->organization_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Teacher/Courses/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(StoreCourseRequest $request, CreateCourseAction $action): RedirectResponse
    {
        $this->authorize('create', Course::class);

        $course = $action->handle($request->user(), $request->validated());

        return redirect()
            ->route('teacher.courses.index')
            ->with('success', "\"{$course->title}\" was created as a draft.");
    }
}
