<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_organisation_roles', function (Blueprint $table) {
            $table->foreignId('organisation_role_id')
                ->nullable()
                ->after('organisation_unit_id')
                ->constrained('organisation_roles')
                ->nullOnDelete();
            $table->index('organisation_role_id');
        });
    }

    public function down(): void
    {
        // The FK is nullable for legacy assignments, but populated values may
        // now be the only link to a role. Never discard those references.
        if (DB::table('user_organisation_roles')->whereNotNull('organisation_role_id')->exists()) {
            throw new RuntimeException('Cannot roll back organisation_role_id while user organisation role references exist. Clear or migrate those references explicitly first.');
        }

        Schema::table('user_organisation_roles', function (Blueprint $table) {
            $table->dropForeign(['organisation_role_id']);
            $table->dropIndex(['organisation_role_id']);
            $table->dropColumn('organisation_role_id');
        });
    }
};
