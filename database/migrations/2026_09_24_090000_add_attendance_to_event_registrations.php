<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('event_registrations')) {
            return;
        }

        Schema::table('event_registrations', function (Blueprint $table): void {
            if (! Schema::hasColumn('event_registrations', 'attendance_status')) {
                $table->string('attendance_status', 20)->default('registered')->after('payment_status');
            }
            if (! Schema::hasColumn('event_registrations', 'attended_at')) {
                $table->timestamp('attended_at')->nullable()->after('attendance_status');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('event_registrations')) {
            return;
        }

        Schema::table('event_registrations', function (Blueprint $table): void {
            $columns = [];
            if (Schema::hasColumn('event_registrations', 'attended_at')) $columns[] = 'attended_at';
            if (Schema::hasColumn('event_registrations', 'attendance_status')) $columns[] = 'attendance_status';
            if ($columns !== []) $table->dropColumn($columns);
        });
    }
};
