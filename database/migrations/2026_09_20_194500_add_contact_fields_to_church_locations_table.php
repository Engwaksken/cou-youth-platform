<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('church_locations')) {
            return;
        }

        Schema::table('church_locations', function (Blueprint $table): void {
            if (! Schema::hasColumn('church_locations', 'phone')) {
                $table->string('phone', 40)->nullable()->after('youth_fellowship_times');
            }

            if (! Schema::hasColumn('church_locations', 'email')) {
                $table->string('email', 190)->nullable()->after('phone');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('church_locations')) {
            return;
        }

        Schema::table('church_locations', function (Blueprint $table): void {
            if (Schema::hasColumn('church_locations', 'email')) {
                $table->dropColumn('email');
            }

            if (Schema::hasColumn('church_locations', 'phone')) {
                $table->dropColumn('phone');
            }
        });
    }
};
