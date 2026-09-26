<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AiSettingController;
use App\Http\Controllers\Admin\BulkMessageController;
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
use App\Http\Controllers\Admin\PageContentController;
use App\Http\Controllers\Admin\PaymentGatewayController;
use App\Http\Controllers\Admin\PlanningFinanceController;
use App\Http\Controllers\Admin\PrayerRequestController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\SystemHealthController;
use App\Http\Controllers\Api\V1\ChatbotController as YouthAssistantController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\YouthAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\YouthPortalController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/news', [PublicSiteController::class, 'news'])->name('public.news');
Route::get('/events', [PublicSiteController::class, 'events'])->name('public.events');
Route::get('/events/{event}', [PublicSiteController::class, 'eventShow'])->name('public.events.show');
Route::get('/courses', [PublicSiteController::class, 'courses'])->name('public.courses');
Route::get('/courses/{course}', [PublicSiteController::class, 'courseShow'])->name('public.courses.show');
Route::get('/life-groups', [PublicSiteController::class, 'lifeGroups'])->name('public.life-groups');
Route::get('/churches', [PublicSiteController::class, 'churches'])->name('public.churches');
Route::get('/donate', [PublicSiteController::class, 'donate'])->name('public.donate');
Route::get('/about', [PublicSiteController::class, 'about'])->name('public.about');
Route::get('/e/{token}', [PublicSiteController::class, 'registerByToken'])->name('public.event.register');
Route::post('/e/{token}', [PublicSiteController::class, 'storeRegistrationByToken'])->name('public.event.register.store');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [YouthAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [YouthAuthController::class, 'login'])->middleware('throttle:youth-login')->name('login.attempt');
    Route::get('/signup', [YouthAuthController::class, 'showRegister'])->name('register');
    Route::post('/signup', [YouthAuthController::class, 'register'])->middleware('throttle:registration')->name('register.store');
    Route::get('/forgot-password', [YouthAuthController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [YouthAuthController::class, 'requestReset'])->middleware('throttle:password-recovery')->name('password.email');
    Route::get('/reset-password', [YouthAuthController::class, 'showReset'])->name('password.reset.form');
    Route::post('/reset-password', [YouthAuthController::class, 'resetPassword'])->middleware('throttle:password-recovery')->name('password.update');
    Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/admin/login', [AdminAuthController::class, 'login'])->middleware('throttle:admin-login')->name('admin.login.attempt');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [YouthAuthController::class, 'logout'])->name('logout');
    Route::post('/youth-assistant', [YouthAssistantController::class, 'reply'])->middleware('throttle:30,1')->name('youth-assistant.reply');

    Route::get('/dashboard', [YouthPortalController::class, 'dashboard'])->name('youth.dashboard');
    Route::get('/profile', [YouthPortalController::class, 'profile'])->name('youth.profile');
    Route::put('/profile', [YouthPortalController::class, 'updateProfile'])->name('youth.profile.update');
    Route::get('/my-life-groups', [YouthPortalController::class, 'lifeGroups'])->name('youth.life-groups');
    Route::post('/my-life-groups/{lifeGroup}/join', [YouthPortalController::class, 'joinLifeGroup'])->name('youth.life-groups.join');
    Route::delete('/my-life-groups/{lifeGroup}/leave', [YouthPortalController::class, 'leaveLifeGroup'])->name('youth.life-groups.leave');
    Route::get('/notifications', [YouthPortalController::class, 'notifications'])->name('youth.notifications');
    Route::patch('/notifications/read-all', [YouthPortalController::class, 'readAllNotifications'])->name('youth.notifications.read-all');
    Route::patch('/notifications/{receipt}/read', [YouthPortalController::class, 'readNotification'])->name('youth.notifications.read');
    Route::get('/my-learning', [YouthPortalController::class, 'learning'])->name('youth.learning');
    Route::get('/media-services', [YouthPortalController::class, 'media'])->name('youth.media');
    Route::get('/annual-theme', [YouthPortalController::class, 'annualTheme'])->name('youth.annual-theme');
    Route::get('/calendar', [YouthPortalController::class, 'calendar'])->name('youth.calendar');

    Route::get('/prayer', [PublicSiteController::class, 'prayer'])->name('public.prayer');
    Route::post('/prayer', [PublicSiteController::class, 'storePrayer'])->middleware('throttle:10,1')->name('public.prayer.store');
});

Route::middleware(['auth', 'cms.access'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('organisation-units', OrganisationUnitController::class)->except(['show', 'create', 'edit']);
    Route::resource('content', ContentController::class)->except(['show', 'create', 'edit']);
    Route::resource('events', EventController::class)->except(['show', 'create', 'edit']);
    Route::put('events/{event}/registrations/{registration}/attendance', [EventController::class, 'updateAttendance'])->name('events.attendance.update');
    Route::resource('life-groups', LifeGroupController::class)->except(['show', 'create', 'edit']);
    Route::resource('courses', CourseController::class)->except(['show', 'create', 'edit']);

    Route::get('work-plans', [PlanningFinanceController::class, 'workPlans'])->name('work-plans.index');
    Route::post('work-plans', [PlanningFinanceController::class, 'storeWorkPlan'])->name('work-plans.store');
    Route::put('work-plans/{id}', [PlanningFinanceController::class, 'updateWorkPlan'])->name('work-plans.update');
    Route::delete('work-plans/{id}', [PlanningFinanceController::class, 'destroyWorkPlan'])->name('work-plans.destroy');

    Route::get('annual-themes', [PlanningFinanceController::class, 'themes'])->name('annual-themes.index');
    Route::post('annual-themes', [PlanningFinanceController::class, 'storeTheme'])->name('annual-themes.store');
    Route::put('annual-themes/{id}', [PlanningFinanceController::class, 'updateTheme'])->name('annual-themes.update');
    Route::delete('annual-themes/{id}', [PlanningFinanceController::class, 'destroyTheme'])->name('annual-themes.destroy');
    Route::get('calendar', [PlanningFinanceController::class, 'calendar'])->name('calendar.index');

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
    Route::get('online-services', [PlanningFinanceController::class, 'onlineServices'])->name('online-services.index');
    Route::post('online-services', [PlanningFinanceController::class, 'storeOnlineService'])->name('online-services.store');
    Route::put('online-services/{id}', [PlanningFinanceController::class, 'updateOnlineService'])->name('online-services.update');
    Route::delete('online-services/{id}', [PlanningFinanceController::class, 'destroyOnlineService'])->name('online-services.destroy');

    Route::resource('church-locations', ChurchLocationController::class)->except(['show', 'create', 'edit']);
    Route::resource('payment-gateways', PaymentGatewayController::class)->except(['show', 'create', 'edit']);

    Route::get('donations', [DonationController::class, 'index'])->name('donations.index');
    Route::post('donations/campaigns', [DonationController::class, 'storeCampaign'])->name('donations.campaigns.store');
    Route::put('donations/campaigns/{campaign}', [DonationController::class, 'updateCampaign'])->name('donations.campaigns.update');
    Route::delete('donations/campaigns/{campaign}', [DonationController::class, 'destroyCampaign'])->name('donations.campaigns.destroy');

    Route::get('membership-fees', [PlanningFinanceController::class, 'membershipFees'])->name('membership-fees.index');
    Route::post('membership-fees', [PlanningFinanceController::class, 'storeMembershipFee'])->name('membership-fees.store');
    Route::put('membership-fees/{id}', [PlanningFinanceController::class, 'updateMembershipFee'])->name('membership-fees.update');
    Route::delete('membership-fees/{id}', [PlanningFinanceController::class, 'destroyMembershipFee'])->name('membership-fees.destroy');
    Route::post('membership-fees/payments', [PlanningFinanceController::class, 'recordMembershipPayment'])->name('membership-fees.payments.store');
    Route::get('financial-reports', [PlanningFinanceController::class, 'financialReports'])->name('financial-reports.index');

    Route::get('ai', [AiSettingController::class, 'index'])->name('ai.index');
    Route::post('ai/settings', [AiSettingController::class, 'store'])->name('ai.settings.store');
    Route::put('ai/settings/{aiSetting}', [AiSettingController::class, 'update'])->name('ai.settings.update');
    Route::post('ai/settings/{aiSetting}/activate', [AiSettingController::class, 'activate'])->name('ai.settings.activate');
    Route::delete('ai/settings/{aiSetting}', [AiSettingController::class, 'destroy'])->name('ai.settings.destroy');
    Route::delete('ai/settings', [AiSettingController::class, 'bulkDestroySettings'])->name('ai.settings.bulk-destroy');
    Route::delete('ai/usage', [AiSettingController::class, 'bulkDestroyUsage'])->name('ai.usage.bulk-destroy');
    Route::post('ai/test', [AiSettingController::class, 'test'])->name('ai.test');

    Route::get('prayer', [PrayerRequestController::class, 'index'])->name('prayer.index');
    Route::put('prayer/{prayerRequest}', [PrayerRequestController::class, 'update'])->name('prayer.update');
    Route::get('moderation', [ModerationController::class, 'index'])->name('moderation.index');
    Route::put('moderation/{report}', [ModerationController::class, 'resolve'])->name('moderation.resolve');
    Route::post('comments/{comment}/approve', [ModerationController::class, 'approveComment'])->name('comments.approve');
    Route::post('comments/{comment}/hide', [ModerationController::class, 'hideComment'])->name('comments.hide');
    Route::delete('comments/{comment}', [ModerationController::class, 'destroyComment'])->name('comments.destroy');
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('system/health', [SystemHealthController::class, 'index'])->name('system.health');
    Route::get('guardian-consents', [GuardianConsentController::class, 'index'])->name('guardian-consents.index');
    Route::post('guardian-consents/{guardianConsent}/approve', [GuardianConsentController::class, 'approve'])->name('guardian-consents.approve');
    Route::post('guardian-consents/{guardianConsent}/reject', [GuardianConsentController::class, 'reject'])->name('guardian-consents.reject');
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications', [NotificationController::class, 'store'])->name('notifications.store');
    Route::put('notifications/{notification}', [NotificationController::class, 'update'])->name('notifications.update');
    Route::delete('notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::get('page-content', [PageContentController::class, 'index'])->name('page-content.index');
    Route::post('page-content/slides', [PageContentController::class, 'storeSlide'])->name('page-content.slides.store');
    Route::put('page-content/slides/{slide}', [PageContentController::class, 'updateSlide'])->name('page-content.slides.update');
    Route::delete('page-content/slides/{slide}', [PageContentController::class, 'destroySlide'])->name('page-content.slides.destroy');
    Route::post('page-content/cards', [PageContentController::class, 'storeCard'])->name('page-content.cards.store');
    Route::put('page-content/cards/{card}', [PageContentController::class, 'updateCard'])->name('page-content.cards.update');
    Route::delete('page-content/cards/{card}', [PageContentController::class, 'destroyCard'])->name('page-content.cards.destroy');
    Route::get('settings', [SiteSettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SiteSettingController::class, 'update'])->name('settings.update');
    Route::get('settings/backup', [SiteSettingController::class, 'backup'])->name('settings.backup');
    Route::post('settings/backup-remote', [SiteSettingController::class, 'backupRemote'])->name('settings.backup-remote');
    Route::get('bulk-messages', [BulkMessageController::class, 'index'])->name('bulk.index');
    Route::post('bulk-messages', [BulkMessageController::class, 'store'])->name('bulk.store');
});
