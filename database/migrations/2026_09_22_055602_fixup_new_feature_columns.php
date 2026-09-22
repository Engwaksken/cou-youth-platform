<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('page_slides', function (Blueprint $t) {
            if (!Schema::hasColumn('page_slides','page')) $t->string('page')->default('home');
            if (!Schema::hasColumn('page_slides','title')) $t->string('title')->nullable();
            if (!Schema::hasColumn('page_slides','subtitle')) $t->string('subtitle')->nullable();
            if (!Schema::hasColumn('page_slides','media_type')) $t->string('media_type')->default('image');
            if (!Schema::hasColumn('page_slides','media_path')) $t->string('media_path')->nullable();
            if (!Schema::hasColumn('page_slides','link_url')) $t->string('link_url')->nullable();
            if (!Schema::hasColumn('page_slides','sort_order')) $t->unsignedInteger('sort_order')->default(0);
            if (!Schema::hasColumn('page_slides','is_active')) $t->boolean('is_active')->default(true);
        });
        Schema::table('page_cards', function (Blueprint $t) {
            if (!Schema::hasColumn('page_cards','page')) $t->string('page')->default('home');
            if (!Schema::hasColumn('page_cards','title')) $t->string('title')->nullable();
            if (!Schema::hasColumn('page_cards','body')) $t->text('body')->nullable();
            if (!Schema::hasColumn('page_cards','icon')) $t->string('icon')->nullable();
            if (!Schema::hasColumn('page_cards','image_path')) $t->string('image_path')->nullable();
            if (!Schema::hasColumn('page_cards','link_url')) $t->string('link_url')->nullable();
            if (!Schema::hasColumn('page_cards','sort_order')) $t->unsignedInteger('sort_order')->default(0);
            if (!Schema::hasColumn('page_cards','is_active')) $t->boolean('is_active')->default(true);
        });
        Schema::table('content_comments', function (Blueprint $t) {
            if (!Schema::hasColumn('content_comments','content_id')) $t->foreignId('content_id')->constrained('contents')->cascadeOnDelete();
            if (!Schema::hasColumn('content_comments','user_id')) $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            if (!Schema::hasColumn('content_comments','guest_name')) $t->string('guest_name')->nullable();
            if (!Schema::hasColumn('content_comments','body')) $t->text('body')->nullable();
            if (!Schema::hasColumn('content_comments','status')) $t->string('status')->default('pending');
        });
        Schema::table('bulk_messages', function (Blueprint $t) {
            if (!Schema::hasColumn('bulk_messages','channel')) $t->string('channel')->default('sms');
            if (!Schema::hasColumn('bulk_messages','subject')) $t->string('subject')->nullable();
            if (!Schema::hasColumn('bulk_messages','body')) $t->text('body')->nullable();
            if (!Schema::hasColumn('bulk_messages','audience')) $t->string('audience')->default('all');
            if (!Schema::hasColumn('bulk_messages','recipient_count')) $t->unsignedInteger('recipient_count')->default(0);
            if (!Schema::hasColumn('bulk_messages','status')) $t->string('status')->default('sent');
            if (!Schema::hasColumn('bulk_messages','created_by')) $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        });
        Schema::table('site_settings', function (Blueprint $t) {
            if (!Schema::hasColumn('site_settings','key')) $t->string('key')->unique();
            if (!Schema::hasColumn('site_settings','value')) $t->text('value')->nullable();
        });
        Schema::table('events', function (Blueprint $t) {
            if (!Schema::hasColumn('events','qr_token')) $t->string('qr_token',64)->nullable()->unique();
            if (!Schema::hasColumn('events','allow_external_registration')) $t->boolean('allow_external_registration')->default(false);
            if (!Schema::hasColumn('events','external_registration_url')) $t->string('external_registration_url',500)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
