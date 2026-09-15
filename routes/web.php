<?php
use App\Http\Controllers\Admin\{ChurchLocationController,ContentController,CourseController,DashboardController,EventController,GuardianConsentController,LifeGroupController,MediaAssetController,ModerationController,NotificationController,OrganisationUnitController,PaymentGatewayController,PrayerRequestController,QuizController,ReportController,SystemHealthController};
use Illuminate\Support\Facades\Route;
Route::middleware(['auth','cms.access'])->prefix('admin')->name('admin.')->group(function(){
 Route::get('/',[DashboardController::class,'index'])->name('dashboard');
 Route::resource('organisation-units',OrganisationUnitController::class)->except(['show','create','edit']);
 Route::resource('content',ContentController::class)->except(['show','create','edit']);
 Route::resource('events',EventController::class)->except(['show','create','edit']);
 Route::resource('life-groups',LifeGroupController::class)->except(['show','create','edit']);
 Route::resource('courses',CourseController::class)->except(['show','create','edit']);
 Route::post('courses/{course}/lessons',[CourseController::class,'storeLesson'])->name('courses.lessons.store');
 Route::delete('courses/{course}/lessons/{lesson}',[CourseController::class,'destroyLesson'])->name('courses.lessons.destroy');
 Route::get('courses/{course}/quizzes',[QuizController::class,'index'])->name('courses.quizzes.index');
 Route::post('courses/{course}/quizzes',[QuizController::class,'store'])->name('courses.quizzes.store');
 Route::post('quizzes/{quiz}/questions',[QuizController::class,'storeQuestion'])->name('quizzes.questions.store');
 Route::delete('quizzes/{quiz}',[QuizController::class,'destroy'])->name('quizzes.destroy');
 Route::delete('quizzes/{quiz}/questions/{question}',[QuizController::class,'destroyQuestion'])->name('quizzes.questions.destroy');
 Route::resource('media',MediaAssetController::class)->only(['index','store','destroy']);
 Route::resource('church-locations',ChurchLocationController::class)->except(['show','create','edit']);
 Route::resource('payment-gateways',PaymentGatewayController::class)->except(['show','create','edit']);
 Route::get('prayer',[PrayerRequestController::class,'index'])->name('prayer.index'); Route::put('prayer/{prayerRequest}',[PrayerRequestController::class,'update'])->name('prayer.update');
 Route::get('moderation',[ModerationController::class,'index'])->name('moderation.index'); Route::put('moderation/{report}',[ModerationController::class,'resolve'])->name('moderation.resolve');
 Route::get('reports',[ReportController::class,'index'])->name('reports.index');
 Route::get('system/health',[SystemHealthController::class,'index'])->name('system.health');
 Route::get('guardian-consents',[GuardianConsentController::class,'index'])->name('guardian-consents.index'); Route::post('guardian-consents/{guardianConsent}/approve',[GuardianConsentController::class,'approve'])->name('guardian-consents.approve'); Route::post('guardian-consents/{guardianConsent}/reject',[GuardianConsentController::class,'reject'])->name('guardian-consents.reject');
 Route::get('notifications',[NotificationController::class,'index'])->name('notifications.index'); Route::post('notifications',[NotificationController::class,'store'])->name('notifications.store');
});
