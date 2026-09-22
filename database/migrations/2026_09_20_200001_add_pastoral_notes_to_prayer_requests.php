<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prayer_requests', function (Blueprint $table): void {
            $table->text('pastoral_notes')->nullable()->after('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::table('prayer_requests', function (Blueprint $table): void {
            $table->dropColumn('pastoral_notes');
        });
    }
};
