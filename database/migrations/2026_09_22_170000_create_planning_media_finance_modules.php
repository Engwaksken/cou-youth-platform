<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annual_work_plans', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->string('title');
            $table->text('objective')->nullable();
            $table->text('activity')->nullable();
            $table->string('responsible_person')->nullable();
            $table->string('department')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('budget', 15, 2)->default(0);
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->string('status', 30)->default('planned');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['year', 'status']);
        });

        Schema::create('annual_themes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('year')->unique();
            $table->string('theme');
            $table->string('scripture_reference')->nullable();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_published')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('online_services', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('platform', 50)->default('YouTube');
            $table->string('stream_url');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->string('speaker')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->string('recurrence', 40)->nullable();
            $table->string('status', 30)->default('scheduled');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['starts_at', 'status']);
        });

        Schema::create('membership_fees', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedSmallInteger('year');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('UGX');
            $table->foreignId('organisation_unit_id')->nullable()->constrained('organisation_units')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['year', 'is_active']);
        });

        Schema::create('membership_fee_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('membership_fee_id')->constrained('membership_fees')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('member_name');
            $table->string('member_reference')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('UGX');
            $table->string('payment_method', 50)->nullable();
            $table->string('reference')->nullable();
            $table->dateTime('paid_at');
            $table->string('status', 30)->default('paid');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['paid_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_fee_payments');
        Schema::dropIfExists('membership_fees');
        Schema::dropIfExists('online_services');
        Schema::dropIfExists('annual_themes');
        Schema::dropIfExists('annual_work_plans');
    }
};
