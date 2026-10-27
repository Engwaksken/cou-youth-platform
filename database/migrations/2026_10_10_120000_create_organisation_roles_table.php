<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();
        if (! in_array($driver, ['mysql', 'mariadb', 'pgsql', 'sqlite'], true)) {
            throw new RuntimeException("Unique global role names are not configured for database driver [{$driver}].");
        }

        if (! Schema::hasTable('organisation_roles')) {
            Schema::create('organisation_roles', function (Blueprint $table) use ($driver) {
                $table->id();
                $table->foreignId('organisation_unit_id')
                    ->nullable()
                    ->constrained('organisation_units')
                    ->nullOnDelete();
                $table->string('name');
                // MySQL 8 rejects literal defaults on JSON columns; it needs an expression default.
                $table->json('permissions')->default(in_array($driver, ['mysql', 'mariadb'], true)
                    ? new Expression('(JSON_ARRAY())')
                    : '[]');
                $table->timestamps();
                $table->index('organisation_unit_id');
            });
        } else {
            // A previous attempt may have created the table but failed before
            // adding its uniqueness constraints. Never assume an existing
            // table is ours, or modify it until its basic shape is verified.
            $required = ['id', 'organisation_unit_id', 'name', 'permissions', 'created_at', 'updated_at'];
            $missing = array_values(array_filter($required, fn ($column) => ! Schema::hasColumn('organisation_roles', $column)));
            if ($missing !== []) {
                throw new RuntimeException('Cannot safely resume organisation_roles migration: existing table is missing expected columns: '.implode(', ', $missing).'. Inspect the table and resolve the schema mismatch manually; no data was changed.');
            }
        }

        $duplicates = DB::table('organisation_roles')
            ->select('organisation_unit_id', 'name', DB::raw('COUNT(*) AS duplicate_count'))
            ->groupBy('organisation_unit_id', 'name')
            ->havingRaw('COUNT(*) > 1')
            ->exists();
        if ($duplicates) {
            throw new RuntimeException('Cannot enforce organisation role name uniqueness: duplicate (organisation_unit_id, name) values already exist, including global names. Resolve duplicate records before rerunning this migration.');
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            // NULL values in this generated key allow unit-scoped rows through
            // while making global names unique. Unlike COALESCE-to-zero this
            // does not require a generated BIGINT expression or reserve an ID.
            $columns = Schema::getColumnListing('organisation_roles');
            if (! in_array('global_name_key', $columns, true)) {
                DB::statement('ALTER TABLE organisation_roles ADD global_name_key VARCHAR(255) GENERATED ALWAYS AS (CASE WHEN organisation_unit_id IS NULL THEN name ELSE NULL END) VIRTUAL');
            } else {
                $column = DB::selectOne('SELECT DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, IS_NULLABLE, EXTRA, GENERATION_EXPRESSION FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?', ['organisation_roles', 'global_name_key']);
                $expression = $column ? preg_replace('/[\s`()]+/', '', strtolower((string) $column->GENERATION_EXPRESSION)) : '';
                if (! $column || $column->DATA_TYPE !== 'varchar'
                    || (int) $column->CHARACTER_MAXIMUM_LENGTH !== 255
                    || $column->IS_NULLABLE !== 'YES'
                    || stripos($column->EXTRA, 'virtual generated') === false
                    || $expression !== 'casewhenorganisation_unit_idisnullthennameelsenullend') {
                    throw new RuntimeException('Cannot safely resume organisation_roles migration: global_name_key exists but is not the expected generated VARCHAR column. Inspect and repair the schema manually.');
                }
            }
            $this->ensureIndex('organisation_roles_scope_name_unique', ['organisation_unit_id', 'name']);
            $this->ensureIndex('organisation_roles_global_name_unique', ['global_name_key']);
        } else {
            $this->ensurePartialIndexes();
        }
    }

    private function ensureIndex(string $name, array $columns): void
    {
        $indexes = Schema::getIndexes('organisation_roles');
        $existing = collect($indexes)->firstWhere('name', $name);
        if ($existing) {
            if (! ($existing['unique'] ?? false) || ($existing['columns'] ?? []) !== $columns) {
                throw new RuntimeException("Cannot safely resume organisation_roles migration: index [{$name}] has an unexpected definition.");
            }
            return;
        }

        Schema::table('organisation_roles', function (Blueprint $table) use ($name, $columns) {
            $table->unique($columns, $name);
        });
    }

    private function ensurePartialIndexes(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            $indexes = DB::select("SELECT name AS indexname, sql AS indexdef FROM sqlite_master WHERE type = 'index' AND tbl_name = 'organisation_roles'");
        } elseif ($driver === 'pgsql') {
            $indexes = DB::select("SELECT indexname, replace(indexdef, quote_ident(schemaname) || '.', '') AS indexdef FROM pg_indexes WHERE schemaname = current_schema() AND tablename = 'organisation_roles'");
        } else {
            throw new RuntimeException("Cannot ensure partial organisation_roles indexes for unsupported database driver [{$driver}].");
        }
        $known = collect($indexes)->keyBy('indexname');
        foreach ([
            'organisation_roles_scope_name_unique' => 'organisation_unit_id, name) WHERE organisation_unit_id IS NOT NULL',
            'organisation_roles_global_name_unique' => 'name) WHERE organisation_unit_id IS NULL',
        ] as $name => $definition) {
            if ($known->has($name)) {
                // PostgreSQL wraps predicates in parentheses and quotes identifiers.
                $normalize = static fn (string $sql): string => preg_replace('/[\s"`()]+/', '', strtolower($sql));
                $actual = $normalize((string) $known[$name]->indexdef);
                $expected = $normalize("CREATE UNIQUE INDEX {$name} ON organisation_roles ({$definition}");
                $actual = str_replace('usingbtree', '', $actual);
                if ($actual !== $expected) {
                    throw new RuntimeException("Cannot safely resume organisation_roles migration: index [{$name}] has an unexpected definition.");
                }
                continue;
            }
            DB::statement("CREATE UNIQUE INDEX {$name} ON organisation_roles ({$definition}");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('organisation_roles');
    }
};
