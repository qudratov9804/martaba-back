<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Services\Analytics\TeacherAnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request, TeacherAnalyticsService $service): Response
    {
        return Inertia::render('Teacher/Dashboard', [
            'overview' => $service->overview($request->user()),
        ]);
    }
}
