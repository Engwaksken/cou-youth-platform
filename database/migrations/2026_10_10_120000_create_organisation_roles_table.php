<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organisation_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_unit_id')
                ->nullable()
                ->constrained('organisation_units')
                ->nullOnDelete();
            $table->string('name');
            $table->json('permissions')->default('[]');
            $table->timestamps();

            $table->index('organisation_unit_id');
        });

        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            // MySQL permits multiple NULL values in a unique index. Normalize
            // the global scope to 0 (an impossible organisation unit ID) so
            // global role names are unique as well as scoped role names.
            Schema::table('organisation_roles', function (Blueprint $table) {
                $table->unsignedBigInteger('organisation_unit_scope')
                    ->storedAs('COALESCE(organisation_unit_id, 0)');
                $table->unique(['organisation_unit_scope', 'name'], 'organisation_roles_scope_name_unique');
            });
        } elseif ($driver === 'pgsql') {
            DB::statement('CREATE UNIQUE INDEX organisation_roles_scope_name_unique ON organisation_roles (organisation_unit_id, name) WHERE organisation_unit_id IS NOT NULL');
            DB::statement('CREATE UNIQUE INDEX organisation_roles_global_name_unique ON organisation_roles (name) WHERE organisation_unit_id IS NULL');
        } elseif ($driver === 'sqlite') {
            DB::statement('CREATE UNIQUE INDEX organisation_roles_scope_name_unique ON organisation_roles (organisation_unit_id, name) WHERE organisation_unit_id IS NOT NULL');
            DB::statement('CREATE UNIQUE INDEX organisation_roles_global_name_unique ON organisation_roles (name) WHERE organisation_unit_id IS NULL');
        } else {
            throw new RuntimeException("Unique global role names are not configured for database driver [{$driver}].");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('organisation_roles');
    }
};
