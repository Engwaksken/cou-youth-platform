<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone', 40)->nullable()->after('email');
            $table->string('location')->nullable()->after('phone');
            // Staff accounts have no youth profile, so their diocese lives on the user.
            $table->foreignId('diocese_id')->nullable()->after('location')->constrained('organisation_units')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('diocese_id');
            $table->dropColumn(['phone', 'location']);
        });
    }
};
