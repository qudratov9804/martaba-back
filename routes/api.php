<?php

use App\Http\Controllers\Api\V1\Admin\AnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Api\V1\Admin\AuditLogController;
use App\Http\Controllers\Api\V1\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\V1\Admin\CertificateController as AdminCertificateController;
use App\Http\Controllers\Api\V1\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Api\V1\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\V1\Admin\OrganizationController;
use App\Http\Controllers\Api\V1\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Media\MediaController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\Public\CategoryController as PublicCategoryController;
use App\Http\Controllers\Api\V1\Public\CertificateController as PublicCertificateController;
use App\Http\Controllers\Api\V1\Public\CourseController as PublicCourseController;
use App\Http\Controllers\Api\V1\Student\AssignmentController as StudentAssignmentController;
use App\Http\Controllers\Api\V1\Student\CertificateController as StudentCertificateController;
use App\Http\Controllers\Api\V1\Student\CheckoutController;
use App\Http\Controllers\Api\V1\Student\EnrollmentController;
use App\Http\Controllers\Api\V1\Student\FavoriteController;
use App\Http\Controllers\Api\V1\Student\LearningController;
use App\Http\Controllers\Api\V1\Student\OrderController as StudentOrderController;
use App\Http\Controllers\Api\V1\Student\QuizAttemptController;
use App\Http\Controllers\Api\V1\Teacher\AnalyticsController as TeacherAnalyticsController;
use App\Http\Controllers\Api\V1\Teacher\AssignmentController as TeacherAssignmentController;
use App\Http\Controllers\Api\V1\Teacher\AssignmentSubmissionController;
use App\Http\Controllers\Api\V1\Teacher\ContentController;
use App\Http\Controllers\Api\V1\Teacher\CourseController as TeacherCourseController;
use App\Http\Controllers\Api\V1\Teacher\LessonController;
use App\Http\Controllers\Api\V1\Teacher\QuestionController;
use App\Http\Controllers\Api\V1\Teacher\QuizAnswerController;
use App\Http\Controllers\Api\V1\Teacher\QuizController as TeacherQuizController;
use App\Http\Controllers\Api\V1\Teacher\SectionController;
use App\Http\Controllers\Api\V1\Teacher\StudentController as TeacherStudentController;
use App\Http\Controllers\Api\V1\Webhooks\PaymentWebhookController;
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

    Route::get('certificates/verify/{code}', [PublicCertificateController::class, 'verify'])->name('certificates.verify');

    Route::post('webhooks/payments/{provider}', [PaymentWebhookController::class, 'handle'])->name('webhooks.payments');

    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::apiResource('organizations', OrganizationController::class);
            Route::apiResource('categories', AdminCategoryController::class);
            Route::apiResource('coupons', AdminCouponController::class);

            Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
            Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');

            Route::get('payments', [AdminPaymentController::class, 'index'])->name('payments.index');
            Route::get('payments/{payment}', [AdminPaymentController::class, 'show'])->name('payments.show');
            Route::post('payments/{payment}/refund', [AdminPaymentController::class, 'refund'])->name('payments.refund');

            Route::get('certificates', [AdminCertificateController::class, 'index'])->name('certificates.index');
            Route::get('certificates/{certificate}', [AdminCertificateController::class, 'show'])->name('certificates.show');
            Route::post('certificates/{certificate}/revoke', [AdminCertificateController::class, 'revoke'])->name('certificates.revoke');
            Route::post('certificates/{certificate}/reissue', [AdminCertificateController::class, 'reissue'])->name('certificates.reissue');

            Route::get('analytics/overview', [AdminAnalyticsController::class, 'overview'])->name('analytics.overview');
            Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
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

            Route::get('courses/{course}/quizzes', [TeacherQuizController::class, 'index'])->name('quizzes.index');
            Route::post('courses/{course}/quizzes', [TeacherQuizController::class, 'store'])->name('quizzes.store');
            Route::get('quizzes/{quiz}', [TeacherQuizController::class, 'show'])->name('quizzes.show');
            Route::put('quizzes/{quiz}', [TeacherQuizController::class, 'update'])->name('quizzes.update');
            Route::delete('quizzes/{quiz}', [TeacherQuizController::class, 'destroy'])->name('quizzes.destroy');

            Route::post('quizzes/{quiz}/questions', [QuestionController::class, 'store'])->name('questions.store');
            Route::put('questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
            Route::delete('questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');

            Route::post('quiz-answers/{answer}/grade', [QuizAnswerController::class, 'grade'])->name('quiz-answers.grade');

            Route::get('courses/{course}/assignments', [TeacherAssignmentController::class, 'index'])->name('assignments.index');
            Route::post('courses/{course}/assignments', [TeacherAssignmentController::class, 'store'])->name('assignments.store');
            Route::get('assignments/{assignment}', [TeacherAssignmentController::class, 'show'])->name('assignments.show');
            Route::put('assignments/{assignment}', [TeacherAssignmentController::class, 'update'])->name('assignments.update');
            Route::delete('assignments/{assignment}', [TeacherAssignmentController::class, 'destroy'])->name('assignments.destroy');

            Route::get('assignments/{assignment}/submissions', [AssignmentSubmissionController::class, 'index'])->name('assignments.submissions.index');
            Route::post('assignment-submissions/{submission}/grade', [AssignmentSubmissionController::class, 'grade'])->name('assignment-submissions.grade');

            Route::get('analytics/overview', [TeacherAnalyticsController::class, 'overview'])->name('analytics.overview');
            Route::get('analytics/revenue', [TeacherAnalyticsController::class, 'revenue'])->name('analytics.revenue');
            Route::get('analytics/courses/{course}', [TeacherAnalyticsController::class, 'course'])->name('analytics.courses');
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

            Route::post('quizzes/{quiz}/attempts', [QuizAttemptController::class, 'store'])->name('quiz-attempts.store');
            Route::get('quiz-attempts/{attempt}', [QuizAttemptController::class, 'show'])->name('quiz-attempts.show');
            Route::post('quiz-attempts/{attempt}/answer', [QuizAttemptController::class, 'answer'])->name('quiz-attempts.answer');
            Route::post('quiz-attempts/{attempt}/submit', [QuizAttemptController::class, 'submit'])->name('quiz-attempts.submit');

            Route::get('assignments', [StudentAssignmentController::class, 'index'])->name('assignments.index');
            Route::post('assignments/{assignment}/submit', [StudentAssignmentController::class, 'submit'])->name('assignments.submit');
            Route::get('submissions/{submission}', [StudentAssignmentController::class, 'showSubmission'])->name('submissions.show');

            Route::get('orders', [StudentOrderController::class, 'index'])->name('orders.index');
            Route::get('orders/{order}', [StudentOrderController::class, 'show'])->name('orders.show');

            Route::get('certificates', [StudentCertificateController::class, 'index'])->name('certificates.index');
            Route::get('certificates/{certificate}', [StudentCertificateController::class, 'show'])->name('certificates.show');
            Route::get('certificates/{certificate}/download', [StudentCertificateController::class, 'download'])->name('certificates.download');
        });

        Route::prefix('checkout')->name('checkout.')->group(function () {
            Route::post('preview', [CheckoutController::class, 'preview'])->name('preview');
            Route::post('orders', [CheckoutController::class, 'store'])->name('orders');
        });

        Route::post('orders/{order}/payments', [PaymentController::class, 'store'])->name('orders.payments.store');
        Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');

        Route::prefix('media')->name('media.')->group(function () {
            Route::get('/', [MediaController::class, 'index'])->name('index');
            Route::post('/', [MediaController::class, 'store'])->name('store');
            Route::get('{media}', [MediaController::class, 'show'])->name('show');
            Route::delete('{media}', [MediaController::class, 'destroy'])->name('destroy');
        });
    });
});
