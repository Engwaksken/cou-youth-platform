<?php

declare(strict_types=1);

use App\Http\Controllers\YouthDonationController;
use App\Http\Controllers\YouthEngagementController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::post('/courses/{course}/enrol', [YouthEngagementController::class, 'enrolCourse'])->name('youth.courses.enrol');
    Route::post('/lessons/{lesson}/complete', [YouthEngagementController::class, 'completeLesson'])->name('youth.lessons.complete');

    Route::get('/my-events', [YouthEngagementController::class, 'events'])->name('youth.events');
    Route::post('/my-events/{event}/register', [YouthEngagementController::class, 'registerEvent'])->name('youth.events.register');

    Route::get('/my-certificates', [YouthEngagementController::class, 'certificates'])->name('youth.certificates');

    Route::get('/notification-preferences', [YouthEngagementController::class, 'preferences'])->name('youth.notification-preferences');
    Route::put('/notification-preferences', [YouthEngagementController::class, 'updatePreferences'])->name('youth.notification-preferences.update');

    Route::get('/my-contributions', [YouthDonationController::class, 'index'])->name('youth.donations');
    Route::post('/my-contributions/donate', [YouthDonationController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('youth.donations.store');
});
