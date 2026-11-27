<?php

use App\Support\ChurchOfUgandaDioceses;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var list<string> */
    private array $tables = ['youth_profiles', 'donations', 'membership_fee_payments'];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'diocese_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table): void {
                // The foreign key also gives MySQL an index on diocese_id for filtering.
                $table->foreignId('diocese_id')->nullable()->constrained('organisation_units')->nullOnDelete();
            });
        }

        // Same idempotent seeding the tests and future installs use.
        ChurchOfUgandaDioceses::seed();
        $this->backfill();
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'diocese_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropConstrainedForeignId('diocese_id');
            });
        }

        // Seeded dioceses are left in place: other records may now point at them.
    }

    /** Fill in dioceses we can already work out from a youth's church unit. */
    private function backfill(): void
    {
        $units = DB::table('organisation_units')->get(['id', 'parent_id', 'type'])->keyBy('id');

        $dioceseOf = function (?int $unitId) use ($units): ?int {
            $seen = [];
            while ($unitId !== null && isset($units[$unitId]) && ! isset($seen[$unitId])) {
                $seen[$unitId] = true;
                $unit = $units[$unitId];
                if ($unit->type === 'diocese') {
                    return (int) $unit->id;
                }
                $unitId = $unit->parent_id !== null ? (int) $unit->parent_id : null;
            }

            return null;
        };

        DB::table('youth_profiles')
            ->whereNull('diocese_id')
            ->whereNotNull('organisation_unit_id')
            ->orderBy('id')
            ->each(function ($profile) use ($dioceseOf): void {
                $dioceseId = $dioceseOf((int) $profile->organisation_unit_id);
                if ($dioceseId !== null) {
                    DB::table('youth_profiles')->where('id', $profile->id)->update(['diocese_id' => $dioceseId]);
                }
            });

        foreach (['donations', 'membership_fee_payments'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            DB::table($tableName)
                ->whereNull('diocese_id')
                ->whereNotNull('user_id')
                ->orderBy('id')
                ->each(function ($row) use ($tableName): void {
                    $dioceseId = DB::table('youth_profiles')->where('user_id', $row->user_id)->value('diocese_id');
                    if ($dioceseId !== null) {
                        DB::table($tableName)->where('id', $row->id)->update(['diocese_id' => $dioceseId]);
                    }
                });
        }
    }
};
