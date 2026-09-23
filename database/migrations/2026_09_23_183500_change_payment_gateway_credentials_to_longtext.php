<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('payment_gateways') || ! Schema::hasColumn('payment_gateways', 'credentials')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE `payment_gateways` MODIFY `credentials` LONGTEXT NULL');
        }
    }

    public function down(): void
    {
        // Do not convert encrypted ciphertext back to JSON. Laravel's encrypted:array
        // cast requires a text-capable column for the encrypted payload.
    }
};
