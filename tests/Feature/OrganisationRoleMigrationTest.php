<?php

namespace Tests\Feature;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrganisationRoleMigrationTest extends TestCase
{
    private string $originalConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalConnection = DB::getDefaultConnection();
        config(['database.connections.role_migration_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'foreign_key_constraints' => true,
        ]]);
        DB::setDefaultConnection('role_migration_test');
        Schema::create('organisation_units', fn (Blueprint $table) => $table->id());
    }

    protected function tearDown(): void
    {
        DB::purge('role_migration_test');
        DB::setDefaultConnection($this->originalConnection);

        parent::tearDown();
    }

    private function migration(): Migration
    {
        return require database_path('migrations/2026_10_10_120000_create_organisation_roles_table.php');
    }

    private function createIncompleteTable(): void
    {
        Schema::create('organisation_roles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('organisation_unit_id')->nullable();
            $table->string('name');
            $table->json('permissions')->default('[]');
            $table->timestamps();
        });
    }

    public function test_migration_can_be_rerun_without_losing_roles(): void
    {
        $migration = $this->migration();
        $migration->up();
        DB::table('organisation_roles')->insert(['name' => 'Global editor']);

        $migration->up();

        $this->assertDatabaseHas('organisation_roles', ['name' => 'Global editor']);
        $this->expectException(QueryException::class);
        DB::table('organisation_roles')->insert(['name' => 'Global editor']);
    }

    public function test_migration_resumes_partial_table_and_preserves_scoped_names(): void
    {
        $this->createIncompleteTable();
        DB::table('organisation_roles')->insert([
            ['organisation_unit_id' => 1, 'name' => 'Editor'],
            ['organisation_unit_id' => 2, 'name' => 'Editor'],
        ]);

        $this->migration()->up();

        $this->assertDatabaseCount('organisation_roles', 2);
        $this->expectException(QueryException::class);
        DB::table('organisation_roles')->insert(['organisation_unit_id' => 1, 'name' => 'Editor']);
    }

    public function test_duplicate_global_names_are_rejected_before_schema_changes(): void
    {
        $this->createIncompleteTable();
        DB::table('organisation_roles')->insert([['name' => 'Editor'], ['name' => 'Editor']]);
        $indexes = Schema::getIndexes('organisation_roles');

        try {
            $this->migration()->up();
            $this->fail('Duplicate global names must prevent migration.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('duplicate', $exception->getMessage());
        }

        $this->assertDatabaseCount('organisation_roles', 2);
        $this->assertSame($indexes, Schema::getIndexes('organisation_roles'));
    }

    public function test_unexpected_index_predicate_is_rejected(): void
    {
        $this->createIncompleteTable();
        DB::statement('CREATE UNIQUE INDEX organisation_roles_scope_name_unique ON organisation_roles (organisation_unit_id, name) WHERE organisation_unit_id IS NOT NULL OR name = \'Editor\'');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('unexpected definition');

        $this->migration()->up();
    }

    public function test_unrelated_table_is_not_modified(): void
    {
        Schema::create('organisation_roles', fn (Blueprint $table) => $table->id());
        DB::table('organisation_roles')->insert(['id' => 1]);

        try {
            $this->migration()->up();
            $this->fail('An unrelated table must not be adopted.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('missing expected columns', $exception->getMessage());
        }

        $this->assertSame(['id'], Schema::getColumnListing('organisation_roles'));
        $this->assertDatabaseCount('organisation_roles', 1);
    }
}
