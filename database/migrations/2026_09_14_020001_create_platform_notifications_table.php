<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('platform_notifications', function(Blueprint $table){
   $table->id(); $table->string('title'); $table->text('body');
   $table->enum('channel',['in_app','push','email','all'])->default('in_app');
   $table->foreignId('organisation_unit_id')->nullable()->constrained()->nullOnDelete();
   $table->enum('age_category',['teen','youth','young_adult','all'])->default('all');
   $table->string('action_url')->nullable(); $table->timestamp('scheduled_at')->nullable();
   $table->timestamp('sent_at')->nullable(); $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
   $table->boolean('is_active')->default(true); $table->timestamps();
  });
  Schema::create('platform_notification_receipts', function(Blueprint $table){
   $table->id(); $table->foreignId('platform_notification_id')->constrained()->cascadeOnDelete();
   $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->timestamp('delivered_at')->nullable();
   $table->timestamp('read_at')->nullable(); $table->timestamps(); $table->unique(['platform_notification_id','user_id']);
  });
 }
 public function down(): void { Schema::dropIfExists('platform_notification_receipts'); Schema::dropIfExists('platform_notifications'); }
};
