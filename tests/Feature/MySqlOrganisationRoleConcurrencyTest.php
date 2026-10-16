<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\OrganisationRole;
use App\Models\OrganisationUnit;
use App\Models\User;
use App\Models\UserOrganisationRole;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Destructive-schema-safe MySQL integration checks. Run only against a throwaway
 * database explicitly named by ROLE_MYSQL_TEST_DATABASE. Opt-in/CI also requires
 * ROLE_MYSQL_TEST_HOST and ROLE_MYSQL_TEST_SERVER_HOST/PORT as independent identity pins.
 */
class MySqlOrganisationRoleConcurrencyTest extends TestCase
{
    /** @var array<string, mixed> */
    private array $temporaryConnections = [];

    protected function setUp(): void
    {
        parent::setUp();

        $explicitMode = getenv('ROLE_MYSQL_TESTS') === '1';
        $ci = getenv('CI') !== false && getenv('CI') !== '' && getenv('CI') !== '0';
        if (getenv('ROLE_MYSQL_TESTS') !== '1'
            || getenv('ROLE_MYSQL_TEST_DATABASE') === false
            || getenv('ROLE_MYSQL_TEST_DATABASE') === ''
            || getenv('ROLE_MYSQL_TEST_HOST') === false || getenv('ROLE_MYSQL_TEST_HOST') === ''
            || getenv('ROLE_MYSQL_TEST_SERVER_HOST') === false || getenv('ROLE_MYSQL_TEST_SERVER_HOST') === ''
            || getenv('ROLE_MYSQL_TEST_SERVER_PORT') === false || getenv('ROLE_MYSQL_TEST_SERVER_PORT') === '') {
            if ($ci) {
                $this->fail('CI requires ROLE_MYSQL_TESTS=1, ROLE_MYSQL_TEST_DATABASE, ROLE_MYSQL_TEST_HOST, ROLE_MYSQL_TEST_SERVER_HOST and ROLE_MYSQL_TEST_SERVER_PORT.');
            }
            $this->markTestSkipped('Set ROLE_MYSQL_TESTS=1 and ROLE_MYSQL_TEST_DATABASE to opt in.');
        }

        $connection = DB::connection();
        $configuredDatabase = (string) $connection->getDatabaseName();
        $dedicatedDatabase = (string) getenv('ROLE_MYSQL_TEST_DATABASE');
        $configuredHost = (string) config('database.connections.'.DB::getDefaultConnection().'.host');
        if ($connection->getDriverName() !== 'mysql'
            || ! hash_equals($dedicatedDatabase, $configuredDatabase)
            || getenv('DB_DATABASE') !== $dedicatedDatabase
            || $configuredHost !== (string) getenv('ROLE_MYSQL_TEST_HOST')
            || getenv('ROLE_MYSQL_TEST_ALLOW_DESTRUCTIVE') !== 'I_UNDERSTAND_THIS_IS_DISPOSABLE') {
            $message = 'MySQL or MariaDB role tests require matching dedicated DB names and the explicit destructive-test acknowledgement.';
            if ($ci || $explicitMode) {
                $this->fail($message);
            }
            $this->markTestSkipped($message);
        }

        // Verify the connected server, not just Laravel's configured driver/name.
        $identity = $connection->selectOne('SELECT DATABASE() AS db, @@hostname AS host, @@port AS port');
        if (! $identity || ! hash_equals($dedicatedDatabase, (string) $identity->db)
            || (string) $identity->host !== (string) getenv('ROLE_MYSQL_TEST_SERVER_HOST')
            || (string) $identity->port !== (string) getenv('ROLE_MYSQL_TEST_SERVER_PORT')) {
            $this->fail('Connected MySQL server identity does not match the explicitly configured disposable database/server.');
        }
    }

    protected function tearDown(): void
    {
        foreach (array_keys($this->temporaryConnections) as $name) {
            DB::purge($name);
            config(['database.connections.'.$name => $this->temporaryConnections[$name]]);
        }
        parent::tearDown();
    }

    private function temporaryConnection(string $suffix): string
    {
        $name = DB::connection()->getName().'_'.$suffix.'_'.bin2hex(random_bytes(5));
        $this->temporaryConnections[$name] = config('database.connections.'.DB::connection()->getName());
        config(['database.connections.'.$name => $this->temporaryConnections[$name]]);
        DB::purge($name);
        return $name;
    }

    private function restoreTemporaryConnection(string $name): void
    {
        DB::purge($name);
        config(['database.connections.'.$name => $this->temporaryConnections[$name]]);
        unset($this->temporaryConnections[$name]);
    }

    public function test_mysql_distinguishes_global_scope_from_organisation_id_zero(): void
    {
        $this->requireMigratedRoleSchema();
        $unit = OrganisationUnit::factory()->create();
        DB::table('organisation_units')->where('id', $unit->id)->update(['id' => 0]);
        $zero = OrganisationRole::query()->create(['organisation_unit_id' => 0, 'name' => 'Scope zero role', 'permissions' => []]);
        // Cross-scope name collisions are intentionally forbidden by the later
        // migration's triggers, even when the scoped organisation ID is zero.
        $global = OrganisationRole::query()->create(['organisation_unit_id' => null, 'name' => 'Global scope role', 'permissions' => []]);

        $this->assertNotSame($zero->id, $global->id);
        $this->assertNull(DB::table('organisation_roles')->where('id', $zero->id)->value('global_name_key'));
        $this->assertSame($global->name, DB::table('organisation_roles')->where('id', $global->id)->value('global_name_key'));
    }

    public function test_mysql_role_schema_uses_nullable_virtual_global_key_and_both_unique_indexes(): void
    {
        $this->requireMigratedRoleSchema();

        $column = DB::selectOne("SHOW COLUMNS FROM organisation_roles LIKE 'global_name_key'");
        $this->assertNotNull($column);
        $this->assertStringContainsString('varchar', strtolower((string) $column->Type));
        $this->assertStringContainsString('generated', strtolower((string) $column->Extra));
        $this->assertStringContainsString('virtual', strtolower((string) $column->Extra));
        $this->assertSame('YES', $column->Null);

        $indexes = collect(Schema::getIndexes('organisation_roles'))->keyBy('name');
        $this->assertSame(['organisation_unit_id', 'name'], $indexes['organisation_roles_scope_name_unique']['columns'] ?? null);
        $this->assertTrue($indexes['organisation_roles_scope_name_unique']['unique'] ?? false);
        $this->assertSame(['global_name_key'], $indexes['organisation_roles_global_name_unique']['columns'] ?? null);
        $this->assertTrue($indexes['organisation_roles_global_name_unique']['unique'] ?? false);
    }

    public function test_mysql_initial_role_migration_can_resume_completed_schema(): void
    {
        $this->requireMigratedRoleSchema();
        $migration = require database_path('migrations/2026_10_10_120000_create_organisation_roles_table.php');
        $migration->up();

        $this->assertTrue(Schema::hasTable('organisation_role_name_locks'));
        $this->assertTrue(Schema::hasColumn('organisation_roles', 'global_name_key'));
    }

    public function test_duplicate_global_name_and_global_scoped_collision_are_rejected(): void
    {
        $this->requireMigratedRoleSchema();
        $name = 'Unique global integration role '.Str::random(12);
        OrganisationRole::query()->create(['organisation_unit_id' => null, 'name' => $name, 'permissions' => []]);

        try {
            OrganisationRole::query()->create(['organisation_unit_id' => null, 'name' => $name, 'permissions' => []]);
            $this->fail('Duplicate global names must fail.');
        } catch (QueryException) {
            $this->assertSame(1, OrganisationRole::query()->where('name', $name)->count());
        }
    }

    public function test_different_scoped_organisations_can_share_name(): void
    {
        $this->requireMigratedRoleSchema();
        $units = OrganisationUnit::factory()->count(2)->create();
        foreach ($units as $unit) {
            OrganisationRole::query()->create(['organisation_unit_id' => $unit->id, 'name' => 'Shared scoped integration role', 'permissions' => []]);
        }

        $this->assertSame(2, OrganisationRole::query()->where('name', 'Shared scoped integration role')->count());
    }

    public function test_same_scope_duplicate_names_are_rejected_by_mysql(): void
    {
        $this->requireMigratedRoleSchema();
        $unit = OrganisationUnit::factory()->create();
        OrganisationRole::query()->create(['organisation_unit_id' => $unit->id, 'name' => 'Duplicate scoped integration role', 'permissions' => []]);

        try {
            OrganisationRole::query()->create(['organisation_unit_id' => $unit->id, 'name' => 'Duplicate scoped integration role', 'permissions' => []]);
            $this->fail('Duplicate names in one scope must fail.');
        } catch (QueryException) {
            $this->assertSame(1, OrganisationRole::query()->where('organisation_unit_id', $unit->id)->where('name', 'Duplicate scoped integration role')->count());
        }
    }

    public function test_global_and_scoped_name_collision_is_rejected_in_either_insert_order(): void
    {
        $this->requireMigratedRoleSchema();
        $unit = OrganisationUnit::factory()->create();
        OrganisationRole::query()->create(['organisation_unit_id' => $unit->id, 'name' => 'Global scoped collision role', 'permissions' => []]);

        try {
            OrganisationRole::query()->create(['organisation_unit_id' => null, 'name' => 'Global scoped collision role', 'permissions' => []]);
            $this->fail('Global and scoped role names must conflict.');
        } catch (QueryException) {
            $this->assertSame(1, OrganisationRole::query()->where('name', 'Global scoped collision role')->count());
        }

        $reverseName = 'Scoped then global collision role';
        OrganisationRole::query()->create(['organisation_unit_id' => $unit->id, 'name' => $reverseName, 'permissions' => []]);
        try {
            OrganisationRole::query()->create(['organisation_unit_id' => null, 'name' => $reverseName, 'permissions' => []]);
            $this->fail('Global insertion must conflict with an existing scoped role.');
        } catch (QueryException) {
            $this->assertSame(1, OrganisationRole::query()->where('name', $reverseName)->count());
        }
    }

    public function test_role_name_serialization_lock_is_transaction_scoped(): void
    {
        $this->requireMigratedRoleSchema();
        $this->assertSame(0, DB::transactionLevel());
        DB::transaction(function (): void {
            DB::table('organisation_role_name_locks')->where('id', 1)->lockForUpdate()->first();
            $this->assertGreaterThan(0, DB::transactionLevel());
        });
        $this->assertSame(0, DB::transactionLevel());
    }

    public function test_role_schema_is_present_after_ci_migration(): void
    {
        $this->assertTrue(Schema::hasTable('organisation_roles'));
        $this->assertTrue(Schema::hasTable('organisation_role_name_locks'));
    }

    public function test_mysql_role_and_assignment_mutations_preserve_catalog_sync_and_block_deletion(): void
    {
        $this->requireMigratedRoleSchema();
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $unit = OrganisationUnit::factory()->create();
        $role = OrganisationRole::query()->create(['organisation_unit_id' => $unit->id, 'name' => 'Before Mysql rename', 'permissions' => ['read']]);
        $user = User::factory()->create();
        $assignment = UserOrganisationRole::query()->create(['user_id' => $user->id, 'organisation_unit_id' => $unit->id, 'organisation_role_id' => $role->id, 'role' => $role->name, 'permissions' => ['read'], 'is_active' => true]);
        $role->name = 'After Mysql rename';
        $role->permissions = ['read', 'write'];
        $role->save();
        $assignment->refresh();
        $this->assertSame('After Mysql rename', $assignment->role);
        $this->assertSame(['read', 'write'], $assignment->permissions);
        $this->expectException(\RuntimeException::class);
        $role->delete();
    }

    public function test_mysql_rename_collision_returns_controlled_conflict(): void
    {
        $this->requireMigratedRoleSchema();
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $unit = OrganisationUnit::factory()->create();
        $first = OrganisationRole::query()->create(['organisation_unit_id' => $unit->id, 'name' => 'Collision source', 'permissions' => []]);
        OrganisationRole::query()->create(['organisation_unit_id' => $unit->id, 'name' => 'Collision target', 'permissions' => []]);
        $response = $this->actingAs($admin)->putJson('/api/admin/roles/'.$first->id, ['name' => 'Collision target', 'organisation_unit_id' => $unit->id, 'permissions' => []]);
        $this->assertSame(409, $response->status());
    }

    public function test_mysql_concurrent_global_and_scoped_inserts_keep_scope_invariant(): void
    {
        $this->requireMigratedRoleSchema();
        $unit = OrganisationUnit::factory()->create();
        $this->assertConcurrentInsertRace('global-scoped', null, (int) $unit->id);
    }

    public function test_mysql_concurrent_same_scope_duplicate_inserts_allow_exactly_one(): void
    {
        $this->requireMigratedRoleSchema();
        $unit = OrganisationUnit::factory()->create();
        $this->assertConcurrentInsertRace('same-scope', (int) $unit->id, (int) $unit->id);
    }

    public function test_role_lock_is_held_for_outer_transaction_lifetime(): void
    {
        $this->requireMigratedRoleSchema();
        $role = OrganisationRole::query()->create(['organisation_unit_id' => null, 'name' => 'Lock lifetime role', 'permissions' => []]);
        DB::beginTransaction();
        try {
            OrganisationRole::withRoleLock((int) $role->id, function (): void {
                $this->assertGreaterThan(0, DB::transactionLevel());
            });
            $this->assertGreaterThan(0, DB::transactionLevel());
        } finally {
            DB::rollBack();
        }
        $this->assertSame(0, DB::transactionLevel());
    }

    public function test_role_name_lock_blocks_an_independent_connection_until_commit(): void
    {
        $this->requireMigratedRoleSchema();
        $secondary = $this->temporaryConnection('role_lock_probe');
        $other = DB::connection($secondary);
        $main = DB::connection();

        try {
            $main->beginTransaction();
            $main->table('organisation_role_name_locks')->where('id', 1)->lockForUpdate()->first();
            $other->statement('SET SESSION innodb_lock_wait_timeout = 1');
            try {
                $other->table('organisation_role_name_locks')->where('id', 1)->lockForUpdate()->first();
                $this->fail('Independent connection unexpectedly acquired the lock before commit.');
            } catch (QueryException $exception) {
                $this->assertStringContainsString('lock', strtolower($exception->getMessage()));
            }
            $main->commit();
            $other->beginTransaction();
            $this->assertNotNull($other->table('organisation_role_name_locks')->where('id', 1)->lockForUpdate()->first());
            $other->commit();
        } finally {
            if ($main->transactionLevel() > 0) $main->rollBack();
            if ($other->transactionLevel() > 0) $other->rollBack();
            $this->restoreTemporaryConnection($secondary);
        }
    }

    public function test_role_name_lock_blocks_concurrent_mutation_until_transaction_finishes(): void
    {
        $this->requireMigratedRoleSchema();
        $role = OrganisationRole::query()->create(['organisation_unit_id' => null, 'name' => 'Concurrent mutation lock '.bin2hex(random_bytes(4)), 'permissions' => []]);
        $secondary = $this->temporaryConnection('role_mutation_probe');
        $main = DB::connection();
        $other = DB::connection($secondary);
        try {
            $main->beginTransaction();
            OrganisationRole::withRoleLock((int) $role->id, static function (): void {});
            $other->statement('SET SESSION innodb_lock_wait_timeout = 1');
            try {
                $other->table('organisation_roles')->where('id', $role->id)->update(['name' => 'Concurrent mutation changed']);
                $this->fail('Independent mutation unexpectedly passed the held role lock.');
            } catch (QueryException $exception) {
                $this->assertStringContainsString('lock', strtolower($exception->getMessage()));
            }
            $main->commit();
            $this->assertSame(1, $other->table('organisation_roles')->where('id', $role->id)->update(['name' => 'Concurrent mutation changed']));
            $this->assertSame('Concurrent mutation changed', OrganisationRole::findOrFail($role->id)->name);
        } finally {
            if ($main->transactionLevel() > 0) $main->rollBack();
            $this->restoreTemporaryConnection($secondary);
        }
    }

    /** Two child processes wait on a filesystem barrier before inserting over distinct MySQL connections. */
    private function assertConcurrentInsertRace(string $label, ?int $firstScope, int $secondScope): void
    {
        if (! function_exists('pcntl_fork')) {
            if (getenv('CI') !== false && getenv('CI') !== '' && getenv('CI') !== '0') {
                $this->fail('CI requires pcntl_fork for process-level MySQL race tests.');
            }
            $this->markTestSkipped('True process-level MySQL race requires pcntl_fork.');
        }
        if (! function_exists('posix_kill') || ! function_exists('pcntl_waitpid') || ! function_exists('pcntl_wifexited')) {
            if (getenv('CI') !== false && getenv('CI') !== '' && getenv('CI') !== '0') $this->fail('CI requires pcntl wait/kill support for race tests.');
            $this->markTestSkipped('Race test requires pcntl wait/kill support.');
        }
        if (! function_exists('tempnam') || ! function_exists('touch') || ! function_exists('file_put_contents')) {
            if (getenv('CI') !== false && getenv('CI') !== '' && getenv('CI') !== '0') $this->fail('CI requires filesystem support for race tests.');
            $this->markTestSkipped('Race test requires filesystem support.');
        }
        $name = 'Concurrent '.$label.' '.Str::random(12);
        $barrier = tempnam(sys_get_temp_dir(), 'role-race-');
        if ($barrier === false) $this->fail('Could not create race-test barrier.');
        $results = [$barrier.'.a', $barrier.'.b'];
        $children = [];
        try {
            foreach ([$firstScope, $secondScope] as $index => $scope) {
                $pid = pcntl_fork();
                if ($pid === -1) {
                    $this->fail('Could not start concurrent role insert process.');
                }
                if ($pid === 0) {
                    try {
                        touch($barrier.'.ready.'.$index);
                        while (! is_file($barrier.'.go')) {
                            usleep(1000);
                        }
                        $connectionName = DB::connection()->getName().'_race_'.$index.'_'.bin2hex(random_bytes(5));
                        config(['database.connections.'.$connectionName => config('database.connections.'.DB::connection()->getName())]);
                        DB::purge($connectionName);
                        OrganisationRole::on($connectionName)->create(['organisation_unit_id' => $scope, 'name' => $name, 'permissions' => []]);
                        file_put_contents($results[$index], 'success');
                    } catch (QueryException) {
                        file_put_contents($results[$index], 'conflict');
                    } catch (\Throwable $exception) {
                        file_put_contents($results[$index], 'error: '.$exception->getMessage());
                    }
                    exit(0);
                }
                $children[] = $pid;
            }
            // All children have reached the barrier before the release signal.
            $deadline = microtime(true) + 10;
            while (count(glob($barrier.'.ready.*') ?: []) < 2 && microtime(true) < $deadline) usleep(1000);
            $this->assertCount(2, glob($barrier.'.ready.*') ?: [], 'Workers did not become ready before release.');
            touch($barrier.'.go');
            foreach ($children as $pid) {
                $deadline = microtime(true) + 15;
                do { $waited = pcntl_waitpid($pid, $status, WNOHANG); if ($waited === 0) usleep(10000); } while ($waited === 0 && microtime(true) < $deadline);
                if ($waited === 0) { posix_kill($pid, SIGKILL); pcntl_waitpid($pid, $status); $this->fail('Race worker timed out and was killed.'); }
                $this->assertTrue(pcntl_wifexited($status));
                $this->assertSame(0, pcntl_wexitstatus($status));
            }
            $outcomes = array_map(static fn (string $path): string => (string) file_get_contents($path), $results);
            sort($outcomes);
            $this->assertSame(['conflict', 'success'], $outcomes);
            $this->assertSame(1, OrganisationRole::query()->where('name', $name)->count());
        } finally {
            if (is_file($barrier.'.go') === false) @touch($barrier.'.go');
            foreach ($children as $pid) {
                $waited = pcntl_waitpid($pid, $status, WNOHANG);
                if ($waited === 0) { posix_kill($pid, SIGKILL); pcntl_waitpid($pid, $status); }
            }
            OrganisationRole::query()->where('name', $name)->delete();
            foreach ([$barrier, $barrier.'.go', $barrier.'.ready.0', $barrier.'.ready.1', ...$results] as $path) {
                if (is_file($path)) unlink($path);
            }
        }
    }

    public function test_role_name_migration_refuses_unsafe_rollback_without_losing_catalog_data(): void
    {
        $this->requireMigratedRoleSchema();
        $unit = OrganisationUnit::factory()->create();
        $name = 'Migration rollback guard '.bin2hex(random_bytes(4));
        OrganisationRole::query()->create(['organisation_unit_id' => $unit->id, 'name' => $name, 'permissions' => []]);
        OrganisationRole::query()->create(['organisation_unit_id' => null, 'name' => $name, 'permissions' => []]);
        $migration = require database_path('migrations/2026_10_10_160000_serialize_organisation_role_name_uniqueness.php');

        try {
            $migration->down();
            $this->fail('Rollback must refuse global/scoped duplicate names before changing schema.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Cannot roll back role-name uniqueness: global/scoped conflicts exist.', $exception->getMessage());
        }

        $this->assertTrue(Schema::hasTable('organisation_role_name_locks'));
        $this->assertSame(2, OrganisationRole::query()->where('name', $name)->count());
    }

    public function test_assignment_reference_migration_refuses_rollback_without_losing_assignment(): void
    {
        $this->requireMigratedRoleSchema();
        $role = OrganisationRole::query()->create(['name' => 'Migration assignment guard '.bin2hex(random_bytes(4)), 'permissions' => []]);
        $assignment = UserOrganisationRole::query()->create([
            'user_id' => User::factory()->create()->id, 'organisation_role_id' => $role->id,
            'role' => $role->name, 'permissions' => [], 'is_active' => true,
        ]);
        $migration = require database_path('migrations/2026_10_10_170000_add_organisation_role_id_to_user_organisation_roles.php');

        try {
            $migration->down();
            $this->fail('Rollback must refuse to discard populated role references.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('Cannot roll back organisation_role_id', $exception->getMessage());
        }

        $this->assertTrue(Schema::hasColumn('user_organisation_roles', 'organisation_role_id'));
        $this->assertDatabaseHas('user_organisation_roles', ['id' => $assignment->id, 'organisation_role_id' => $role->id]);
    }

    private function requireMigratedRoleSchema(): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('organisation_roles')
            || ! \Illuminate\Support\Facades\Schema::hasTable('organisation_role_name_locks')) {
            $this->fail('Apply the role migrations to the explicitly configured disposable MySQL database first.');
        }
    }
}
