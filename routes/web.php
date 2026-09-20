<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AiSettingController;
use App\Http\Controllers\Admin\ChurchLocationController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DonationController;
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

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.attempt');
});
Route::post('/logout', [AdminAuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'cms.access'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('organisation-units', OrganisationUnitController::class)->except(['show', 'create', 'edit']);
    Route::resource('content', ContentController::class)->except(['show', 'create', 'edit']);
    Route::resource('events', EventController::class)->except(['show', 'create', 'edit']);
    Route::resource('life-groups', LifeGroupController::class)->except(['show', 'create', 'edit']);
    Route::resource('courses', CourseController::class)->except(['show', 'create', 'edit']);

    Route::post('courses/{course}/lessons', [CourseController::class, 'storeLesson'])->name('courses.lessons.store');
    Route::delete('courses/{course}/lessons/{lesson}', [CourseController::class, 'destroyLesson'])->name('courses.lessons.destroy');

    Route::get('courses/{course}/quizzes', [QuizController::class, 'index'])->name('courses.quizzes.index');
    Route::post('courses/{course}/quizzes', [QuizController::class, 'store'])->name('courses.quizzes.store');
    Route::put('quizzes/{quiz}', [QuizController::class, 'update'])->name('quizzes.update');
    Route::post('quizzes/{quiz}/questions', [QuizController::class, 'storeQuestion'])->name('quizzes.questions.store');
    Route::put('quizzes/{quiz}/questions/{question}', [QuizController::class, 'updateQuestion'])->name('quizzes.questions.update');
    Route::delete('quizzes/{quiz}', [QuizController::class, 'destroy'])->name('quizzes.destroy');
    Route::delete('quizzes/{quiz}/questions/{question}', [QuizController::class, 'destroyQuestion'])->name('quizzes.questions.destroy');

    Route::resource('media', MediaAssetController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('church-locations', ChurchLocationController::class)->except(['show', 'create', 'edit']);
    Route::resource('payment-gateways', PaymentGatewayController::class)->except(['show', 'create', 'edit']);

    Route::get('donations', [DonationController::class, 'index'])->name('donations.index');
    Route::post('donations/campaigns', [DonationController::class, 'storeCampaign'])->name('donations.campaigns.store');
    Route::put('donations/campaigns/{campaign}', [DonationController::class, 'updateCampaign'])->name('donations.campaigns.update');
    Route::delete('donations/campaigns/{campaign}', [DonationController::class, 'destroyCampaign'])->name('donations.campaigns.destroy');

    Route::get('ai', [AiSettingController::class, 'index'])->name('ai.index');
    Route::put('ai', [AiSettingController::class, 'update'])->name('ai.update');
    Route::post('ai/test', [AiSettingController::class, 'test'])->name('ai.test');

    Route::get('prayer', [PrayerRequestController::class, 'index'])->name('prayer.index');
    Route::put('prayer/{prayerRequest}', [PrayerRequestController::class, 'update'])->name('prayer.update');

    Route::get('moderation', [ModerationController::class, 'index'])->name('moderation.index');
    Route::put('moderation/{report}', [ModerationController::class, 'resolve'])->name('moderation.resolve');

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('system/health', [SystemHealthController::class, 'index'])->name('system.health');

    Route::get('guardian-consents', [GuardianConsentController::class, 'index'])->name('guardian-consents.index');
    Route::post('guardian-consents/{guardianConsent}/approve', [GuardianConsentController::class, 'approve'])->name('guardian-consents.approve');
    Route::post('guardian-consents/{guardianConsent}/reject', [GuardianConsentController::class, 'reject'])->name('guardian-consents.reject');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications', [NotificationController::class, 'store'])->name('notifications.store');
    Route::put('notifications/{notification}', [NotificationController::class, 'update'])->name('notifications.update');
    Route::delete('notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});
