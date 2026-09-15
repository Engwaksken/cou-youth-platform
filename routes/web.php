<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\ChurchLocationController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\GuardianConsentController;
use App\Http\Controllers\Admin\LifeGroupController;
use App\Http\Controllers\Admin\MediaAssetController;
use App\Http\Controllers\Admin\ModerationController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OrganisationUnitController;
use App\Http\Controllers\Admin\PaymentGatewayController;
use App\Http\Controllers\Admin\PrayerRequestController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SystemHealthController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Website
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])
    ->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])
        ->name('login');

    Route::post('/login', [AdminAuthController::class, 'login'])
        ->name('login.attempt');
});

Route::post('/logout', [AdminAuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Admin CMS
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'cms.access',
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('/', [DashboardController::class, 'index'])
            ->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Organisation Units
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'organisation-units',
            OrganisationUnitController::class
        )->except([
            'show',
            'create',
            'edit',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Content
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'content',
            ContentController::class
        )->except([
            'show',
            'create',
            'edit',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Events
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'events',
            EventController::class
        )->except([
            'show',
            'create',
            'edit',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Life Groups
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'life-groups',
            LifeGroupController::class
        )->except([
            'show',
            'create',
            'edit',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Courses
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'courses',
            CourseController::class
        )->except([
            'show',
            'create',
            'edit',
        ]);

        Route::post(
            'courses/{course}/lessons',
            [CourseController::class, 'storeLesson']
        )->name('courses.lessons.store');

        Route::delete(
            'courses/{course}/lessons/{lesson}',
            [CourseController::class, 'destroyLesson']
        )->name('courses.lessons.destroy');

        /*
        |--------------------------------------------------------------------------
        | Quizzes
        |--------------------------------------------------------------------------
        */

        Route::get(
            'courses/{course}/quizzes',
            [QuizController::class, 'index']
        )->name('courses.quizzes.index');

        Route::post(
            'courses/{course}/quizzes',
            [QuizController::class, 'store']
        )->name('courses.quizzes.store');

        Route::post(
            'quizzes/{quiz}/questions',
            [QuizController::class, 'storeQuestion']
        )->name('quizzes.questions.store');

        Route::delete(
            'quizzes/{quiz}',
            [QuizController::class, 'destroy']
        )->name('quizzes.destroy');

        Route::delete(
            'quizzes/{quiz}/questions/{question}',
            [QuizController::class, 'destroyQuestion']
        )->name('quizzes.questions.destroy');

        /*
        |--------------------------------------------------------------------------
        | Media Library
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'media',
            MediaAssetController::class
        )->only([
            'index',
            'store',
            'destroy',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Church Locations
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'church-locations',
            ChurchLocationController::class
        )->except([
            'show',
            'create',
            'edit',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Payment Gateways
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'payment-gateways',
            PaymentGatewayController::class
        )->except([
            'show',
            'create',
            'edit',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Prayer & Pastoral Support
        |--------------------------------------------------------------------------
        */

        Route::get(
            'prayer',
            [PrayerRequestController::class, 'index']
        )->name('prayer.index');

        Route::put(
            'prayer/{prayerRequest}',
            [PrayerRequestController::class, 'update']
        )->name('prayer.update');

        /*
        |--------------------------------------------------------------------------
        | Moderation
        |--------------------------------------------------------------------------
        */

        Route::get(
            'moderation',
            [ModerationController::class, 'index']
        )->name('moderation.index');

        Route::put(
            'moderation/{report}',
            [ModerationController::class, 'resolve']
        )->name('moderation.resolve');

        /*
        |--------------------------------------------------------------------------
        | Reports
        |--------------------------------------------------------------------------
        */

        Route::get(
            'reports',
            [ReportController::class, 'index']
        )->name('reports.index');

        /*
        |--------------------------------------------------------------------------
        | System Health
        |--------------------------------------------------------------------------
        */

        Route::get(
            'system/health',
            [SystemHealthController::class, 'index']
        )->name('system.health');

        /*
        |--------------------------------------------------------------------------
        | Guardian Consents
        |--------------------------------------------------------------------------
        */

        Route::get(
            'guardian-consents',
            [GuardianConsentController::class, 'index']
        )->name('guardian-consents.index');

        Route::post(
            'guardian-consents/{guardianConsent}/approve',
            [GuardianConsentController::class, 'approve']
        )->name('guardian-consents.approve');

        Route::post(
            'guardian-consents/{guardianConsent}/reject',
            [GuardianConsentController::class, 'reject']
        )->name('guardian-consents.reject');

        /*
        |--------------------------------------------------------------------------
        | Notifications
        |--------------------------------------------------------------------------
        */

        Route::get(
            'notifications',
            [NotificationController::class, 'index']
        )->name('notifications.index');

        Route::post(
            'notifications',
            [NotificationController::class, 'store']
        )->name('notifications.store');
    });