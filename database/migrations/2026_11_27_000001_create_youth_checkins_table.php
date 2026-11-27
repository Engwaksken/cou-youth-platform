<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('youth_checkins', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Local calendar day (see cou_youth.engagement_timezone), not a UTC timestamp.
            $table->date('checked_in_on');
            $table->timestamps();

            $table->unique(['user_id', 'checked_in_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('youth_checkins');
    }
};
