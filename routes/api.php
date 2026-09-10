<?php

use App\Http\Controllers\Api\V1\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\V1\Admin\OrganizationController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Media\MediaController;
use App\Http\Controllers\Api\V1\Public\CategoryController as PublicCategoryController;
use App\Http\Controllers\Api\V1\Public\CourseController as PublicCourseController;
use App\Http\Controllers\Api\V1\Student\EnrollmentController;
use App\Http\Controllers\Api\V1\Student\FavoriteController;
use App\Http\Controllers\Api\V1\Student\LearningController;
use App\Http\Controllers\Api\V1\Teacher\ContentController;
use App\Http\Controllers\Api\V1\Teacher\CourseController as TeacherCourseController;
use App\Http\Controllers\Api\V1\Teacher\LessonController;
use App\Http\Controllers\Api\V1\Teacher\SectionController;
use App\Http\Controllers\Api\V1\Teacher\StudentController as TeacherStudentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('register', [AuthController::class, 'register'])->name('register');
        Route::post('login', [AuthController::class, 'login'])->name('login');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout'])->name('logout');
            Route::get('me', [AuthController::class, 'me'])->name('me');
        });
    });

    Route::prefix('public')->name('public.')->group(function () {
        Route::get('categories', [PublicCategoryController::class, 'index'])->name('categories.index');
        Route::get('categories/{slug}', [PublicCategoryController::class, 'show'])->name('categories.show');

        Route::get('courses', [PublicCourseController::class, 'index'])->name('courses.index');
        Route::get('courses/{slug}', [PublicCourseController::class, 'show'])->name('courses.show');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::apiResource('organizations', OrganizationController::class);
            Route::apiResource('categories', AdminCategoryController::class);
        });

        Route::prefix('teacher')->name('teacher.')->group(function () {
            Route::apiResource('courses', TeacherCourseController::class);
            Route::post('courses/{course}/publish', [TeacherCourseController::class, 'publish'])->name('courses.publish');
            Route::post('courses/{course}/unpublish', [TeacherCourseController::class, 'unpublish'])->name('courses.unpublish');
            Route::post('courses/{course}/archive', [TeacherCourseController::class, 'archive'])->name('courses.archive');

            Route::post('courses/{course}/sections', [SectionController::class, 'store'])->name('sections.store');
            Route::put('sections/{section}', [SectionController::class, 'update'])->name('sections.update');
            Route::delete('sections/{section}', [SectionController::class, 'destroy'])->name('sections.destroy');
            Route::post('courses/{course}/sections/reorder', [SectionController::class, 'reorder'])->name('sections.reorder');

            Route::post('sections/{section}/lessons', [LessonController::class, 'store'])->name('lessons.store');
            Route::put('lessons/{lesson}', [LessonController::class, 'update'])->name('lessons.update');
            Route::delete('lessons/{lesson}', [LessonController::class, 'destroy'])->name('lessons.destroy');
            Route::post('sections/{section}/lessons/reorder', [LessonController::class, 'reorder'])->name('lessons.reorder');

            Route::post('lessons/{lesson}/contents', [ContentController::class, 'store'])->name('contents.store');
            Route::put('contents/{content}', [ContentController::class, 'update'])->name('contents.update');
            Route::delete('contents/{content}', [ContentController::class, 'destroy'])->name('contents.destroy');

            Route::get('courses/{course}/students', [TeacherStudentController::class, 'index'])->name('students.index');
        });

        Route::prefix('student')->name('student.')->group(function () {
            Route::get('enrollments', [EnrollmentController::class, 'index'])->name('enrollments.index');
            Route::get('enrollments/{enrollment}', [EnrollmentController::class, 'show'])->name('enrollments.show');
            Route::post('courses/{course}/enroll', [EnrollmentController::class, 'store'])->name('enrollments.store');

            Route::get('courses/{course}/learn', [LearningController::class, 'learn'])->name('learn');
            Route::get('lessons/{lesson}', [LearningController::class, 'showLesson'])->name('lessons.show');
            Route::post('lessons/{lesson}/start', [LearningController::class, 'start'])->name('lessons.start');
            Route::post('lessons/{lesson}/progress', [LearningController::class, 'updateProgress'])->name('lessons.progress');
            Route::post('lessons/{lesson}/complete', [LearningController::class, 'complete'])->name('lessons.complete');

            Route::get('favorites', [FavoriteController::class, 'index'])->name('favorites.index');
            Route::post('favorites/{course}', [FavoriteController::class, 'store'])->name('favorites.store');
            Route::delete('favorites/{course}', [FavoriteController::class, 'destroy'])->name('favorites.destroy');
        });

        Route::prefix('media')->name('media.')->group(function () {
            Route::get('/', [MediaController::class, 'index'])->name('index');
            Route::post('/', [MediaController::class, 'store'])->name('store');
            Route::get('{media}', [MediaController::class, 'show'])->name('show');
            Route::delete('{media}', [MediaController::class, 'destroy'])->name('destroy');
        });
    });
});
