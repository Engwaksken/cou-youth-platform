<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organisation_roles', function (Blueprint $table) {
            $table->dropForeign(['organisation_unit_id']);
            $table->foreign('organisation_unit_id')
                ->references('id')
                ->on('organisation_units')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('organisation_roles', function (Blueprint $table) {
            $table->dropForeign(['organisation_unit_id']);
            $table->foreign('organisation_unit_id')
                ->references('id')
                ->on('organisation_units')
                ->nullOnDelete();
        });
    }
};
