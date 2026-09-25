<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('provider');
            $table->string('currency', 3)->default('UGX');

            // Laravel's encrypted:array cast stores an encrypted string, not JSON.
            // Use LONGTEXT so MySQL/MariaDB does not reject the ciphertext with a JSON constraint.
            $table->longText('credentials')->nullable();

            $table->json('settings')->nullable();
            $table->boolean('is_test_mode')->default(true);
            $table->boolean('is_enabled')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
