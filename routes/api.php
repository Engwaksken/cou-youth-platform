<?php

use App\Http\Controllers\Api\V1\{
    AuthController,
    AuthRecoveryController,
    CertificateController,
    CertificatePdfController,
    ChatbotController,
    ChurchLocatorController,
    ContentController,
    CourseController,
    DeviceController,
    DonationController,
    EventController,
    GuardianConsentController,
    HealthController,
    LifeGroupController,
    MediaController,
    ModerationController,
    NotificationController,
    NotificationPreferenceController,
    OrganisationController,
    PaymentWebhookController,
    PrayerController,
    QuizController,
    ReceiptController,
    RegistrationController,
    ReleaseController,
};
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', HealthController::class)->middleware('throttle:60,1');
    Route::get('/release', [ReleaseController::class, 'show'])->middleware('throttle:60,1');

    Route::post('/register', [RegistrationController::class, 'store']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/password/request', [AuthRecoveryController::class, 'requestPasswordReset']);
    Route::post('/auth/password/reset', [AuthRecoveryController::class, 'resetPassword']);
    Route::post('/auth/otp/request', [AuthRecoveryController::class, 'requestLoginOtp']);
    Route::post('/auth/otp/verify', [AuthRecoveryController::class, 'verifyLoginOtp']);

    Route::post('/payments/webhooks/{gatewaySlug}', [PaymentWebhookController::class, 'handle'])
        ->middleware('throttle:120,1');

    Route::get('/organisation-units', [OrganisationController::class, 'index']);
    Route::get('/content', [ContentController::class, 'index']);
    Route::get('/content/{content}', [ContentController::class, 'show']);
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{event}', [EventController::class, 'show']);
    Route::get('/life-groups', [LifeGroupController::class, 'index']);
    Route::get('/donation-campaigns', [DonationController::class, 'campaigns']);
    Route::get('/payment-gateways', [DonationController::class, 'gateways']);
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/courses/{course}', [CourseController::class, 'show']);
    Route::get('/media', [MediaController::class, 'index']);
    Route::get('/church-locator', [ChurchLocatorController::class, 'index']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::post('/auth/email/request', [AuthRecoveryController::class, 'requestEmailVerification']);
        Route::post('/auth/email/verify', [AuthRecoveryController::class, 'verifyEmail']);

        Route::post('/devices', [DeviceController::class, 'store']);
        Route::delete('/devices', [DeviceController::class, 'destroy']);

        Route::get('/guardian-consent', [GuardianConsentController::class, 'show']);
        Route::post('/guardian-consent', [GuardianConsentController::class, 'store']);

        Route::post('/events/{event}/register', [EventController::class, 'register']);
        Route::post('/life-groups/{lifeGroup}/join', [LifeGroupController::class, 'join']);

        Route::post('/donations', [DonationController::class, 'store']);
        Route::get('/donations/{donation}/receipt', [ReceiptController::class, 'show']);

        Route::post('/chatbot', [ChatbotController::class, 'reply']);

        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/{receipt}/read', [NotificationController::class, 'read']);
        Route::get('/notification-preferences', [NotificationPreferenceController::class, 'show']);
        Route::put('/notification-preferences', [NotificationPreferenceController::class, 'update']);

        Route::post('/courses/{course}/enrol', [CourseController::class, 'enrol']);
        Route::post('/lessons/{lesson}/complete', [CourseController::class, 'completeLesson']);
        Route::get('/quizzes/{quiz}', [QuizController::class, 'show']);
        Route::post('/quizzes/{quiz}/submit', [QuizController::class, 'submit']);
        Route::get('/certificates', [CertificateController::class, 'index']);
        Route::get('/certificates/{certificate}', [CertificateController::class, 'show']);
        Route::get('/certificates/{certificate}/pdf', [CertificatePdfController::class, 'show']);

        Route::get('/prayer-requests', [PrayerController::class, 'index']);
        Route::post('/prayer-requests', [PrayerController::class, 'store']);

        Route::post('/moderation/reports', [ModerationController::class, 'report']);
        Route::get('/moderation/blocked', [ModerationController::class, 'blocked']);
        Route::post('/moderation/users/{user}/block', [ModerationController::class, 'block']);
        Route::delete('/moderation/users/{user}/block', [ModerationController::class, 'unblock']);
    });
});
