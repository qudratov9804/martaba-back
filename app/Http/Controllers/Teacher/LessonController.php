<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\CourseLesson;
use Inertia\Inertia;
use Inertia\Response;

class LessonController extends Controller
{
    public function show(CourseLesson $lesson): Response
    {
        $this->authorize('manageContent', $lesson->course);

        $lesson->load(['course:id,title', 'section:id,title', 'contents.media']);

        return Inertia::render('Teacher/Lessons/Show', [
            'lesson' => $lesson,
        ]);
    }
}
