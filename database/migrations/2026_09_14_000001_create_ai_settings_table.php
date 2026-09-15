<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_settings', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('openai');
            $table->string('model')->nullable();
            $table->text('api_key')->nullable();
            $table->string('api_endpoint')->nullable();
            $table->decimal('temperature', 3, 2)->default(0.30);
            $table->unsignedInteger('max_tokens')->default(1200);
            $table->unsignedInteger('daily_limit')->nullable();
            $table->unsignedInteger('monthly_limit')->nullable();
            $table->unsignedInteger('per_user_daily_limit')->nullable();
            $table->unsignedInteger('timeout_seconds')->default(30);
            $table->unsignedTinyInteger('retry_count')->default(1);
            $table->longText('system_prompt')->nullable();
            $table->longText('safety_prompt')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_settings');
    }
};
