<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Analytics\AdminAnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request, AdminAnalyticsService $service): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'overview' => $service->overview($request->user()->organization_id),
        ]);
    }
}
