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

        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        $database = $connection->getDatabaseName();

        // MariaDB implements JSON as LONGTEXT plus an automatic CHECK constraint.
        // Changing the column type alone can leave that JSON_VALID constraint behind,
        // which rejects Laravel encrypted ciphertext written by encrypted:array.
        $constraints = DB::select(
            <<<'SQL'
                SELECT tc.CONSTRAINT_NAME
                FROM information_schema.TABLE_CONSTRAINTS tc
                LEFT JOIN information_schema.CHECK_CONSTRAINTS cc
                    ON cc.CONSTRAINT_SCHEMA = tc.CONSTRAINT_SCHEMA
                   AND cc.CONSTRAINT_NAME = tc.CONSTRAINT_NAME
                WHERE tc.CONSTRAINT_SCHEMA = ?
                  AND tc.TABLE_NAME = 'payment_gateways'
                  AND tc.CONSTRAINT_TYPE = 'CHECK'
                  AND (
                        tc.CONSTRAINT_NAME LIKE '%credentials%'
                        OR cc.CHECK_CLAUSE LIKE '%credentials%'
                  )
            SQL,
            [$database],
        );

        foreach ($constraints as $constraint) {
            $name = (string) $constraint->CONSTRAINT_NAME;
            $quoted = '`'.str_replace('`', '``', $name).'`';

            try {
                DB::statement("ALTER TABLE `payment_gateways` DROP CONSTRAINT {$quoted}");
            } catch (Throwable) {
                // Older MySQL/MariaDB variants may use DROP CHECK syntax instead.
                DB::statement("ALTER TABLE `payment_gateways` DROP CHECK {$quoted}");
            }
        }

        DB::statement('ALTER TABLE `payment_gateways` MODIFY `credentials` LONGTEXT NULL');
    }

    public function down(): void
    {
        // Intentionally irreversible. Re-adding a JSON validity constraint would make
        // Laravel encrypted:array ciphertext impossible to persist.
    }
};
