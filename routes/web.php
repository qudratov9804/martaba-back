<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CertificateController as AdminCertificateController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\OrganizationController as AdminOrganizationController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Teacher\CourseController as TeacherCourseController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function (Request $request) {
    $user = $request->user();

    if ($user->hasRole('super_admin')) {
        return Redirect::route('admin.dashboard');
    }

    if ($user->hasRole('teacher')) {
        return Redirect::route('teacher.dashboard');
    }

    return Redirect::route('profile.edit');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/organizations', [AdminOrganizationController::class, 'index'])->name('organizations.index');
    Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
    Route::get('/coupons', [AdminCouponController::class, 'index'])->name('coupons.index');
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
    Route::get('/certificates', [AdminCertificateController::class, 'index'])->name('certificates.index');
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
});

Route::middleware(['auth', 'verified', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');
    Route::get('/courses', [TeacherCourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/create', [TeacherCourseController::class, 'create'])->name('courses.create');
    Route::post('/courses', [TeacherCourseController::class, 'store'])->name('courses.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
