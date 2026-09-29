<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('event_registrations')) {
            return;
        }

        Schema::table('event_registrations', function (Blueprint $table): void {
            if (! Schema::hasColumn('event_registrations', 'name')) {
                $table->string('name', 160)->nullable()->after('user_id');
            }
            if (! Schema::hasColumn('event_registrations', 'phone')) {
                $table->string('phone', 40)->nullable()->after('name');
            }
            if (! Schema::hasColumn('event_registrations', 'email')) {
                $table->string('email', 200)->nullable()->after('phone');
            }
        });

        // QR/public registrations may be made by guests, so user_id must be nullable.
        Schema::table('event_registrations', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('event_registrations')) {
            return;
        }

        $columns = array_values(array_filter(
            ['name', 'phone', 'email'],
            fn (string $column): bool => Schema::hasColumn('event_registrations', $column)
        ));

        if ($columns !== []) {
            Schema::table('event_registrations', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }

        // user_id deliberately remains nullable on rollback to avoid destroying
        // or invalidating guest registrations created after this migration.
    }
};
