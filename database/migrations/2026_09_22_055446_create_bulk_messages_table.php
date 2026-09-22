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
        Schema::create('bulk_messages', function (Blueprint $table) {
            $table->id();
            $table->string('channel')->default('sms');
            $table->string('subject')->nullable();
            $table->text('body');
            $table->string('audience')->default('all');
            $table->unsignedInteger('recipient_count')->default(0);
            $table->string('status')->default('sent');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_messages');
    }
};
