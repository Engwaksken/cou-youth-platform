<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('organisation_units') || ! Schema::hasColumn('organisation_units', 'type')) {
            return;
        }

        // A VARCHAR keeps the hierarchy extensible and avoids MySQL ENUM failures
        // when legitimate Church unit types are added by the application.
        Schema::table('organisation_units', function (Blueprint $table): void {
            $table->string('type', 40)->change();
        });
    }

    public function down(): void
    {
        // Deliberately do not restore the restrictive ENUM. Existing rows may
        // contain valid newer unit types that an ENUM rollback would truncate.
    }
};
