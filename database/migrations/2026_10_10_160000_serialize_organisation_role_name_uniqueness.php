<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            // The original COALESCE sentinel conflated NULL with organisation
            // id 0. Keep the real nullable scope in the unique index instead.
            // The singleton InnoDB row serializes all name checks, including
            // competing inserts whose target rows do not yet exist.
            DB::statement('CREATE TABLE organisation_role_name_locks (id TINYINT UNSIGNED NOT NULL PRIMARY KEY) ENGINE=InnoDB');
            DB::statement('INSERT INTO organisation_role_name_locks (id) VALUES (1)');
            DB::statement('ALTER TABLE organisation_roles DROP INDEX organisation_roles_scope_name_unique');
            DB::statement('ALTER TABLE organisation_roles DROP COLUMN organisation_unit_scope');
            DB::statement('CREATE UNIQUE INDEX organisation_roles_scope_name_unique ON organisation_roles (organisation_unit_id, name)');
            DB::unprepared('DROP TRIGGER IF EXISTS organisation_roles_name_scope_insert');
            DB::unprepared('DROP TRIGGER IF EXISTS organisation_roles_name_scope_update');
            DB::unprepared("CREATE TRIGGER organisation_roles_name_scope_insert BEFORE INSERT ON organisation_roles FOR EACH ROW BEGIN DECLARE lock_id TINYINT; DECLARE conflict_id BIGINT DEFAULT NULL; DECLARE CONTINUE HANDLER FOR NOT FOUND SET conflict_id = NULL; SELECT id INTO lock_id FROM organisation_role_name_locks WHERE id = 1 FOR UPDATE; SELECT existing.id INTO conflict_id FROM organisation_roles existing WHERE existing.name = NEW.name AND (existing.organisation_unit_id IS NULL OR NEW.organisation_unit_id IS NULL) LIMIT 1 FOR UPDATE; IF conflict_id IS NOT NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'organisation role name conflicts across scopes'; END IF; END");
            DB::unprepared("CREATE TRIGGER organisation_roles_name_scope_update BEFORE UPDATE ON organisation_roles FOR EACH ROW BEGIN DECLARE lock_id TINYINT; DECLARE conflict_id BIGINT DEFAULT NULL; DECLARE CONTINUE HANDLER FOR NOT FOUND SET conflict_id = NULL; SELECT id INTO lock_id FROM organisation_role_name_locks WHERE id = 1 FOR UPDATE; SELECT existing.id INTO conflict_id FROM organisation_roles existing WHERE existing.id <> OLD.id AND existing.name = NEW.name AND (existing.organisation_unit_id IS NULL OR NEW.organisation_unit_id IS NULL) LIMIT 1 FOR UPDATE; IF conflict_id IS NOT NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'organisation role name conflicts across scopes'; END IF; END");
        } elseif ($driver === 'pgsql') {
            // A deferred constraint trigger runs at transaction end, after all writes
            // have completed. At READ COMMITTED its query obtains a fresh snapshot
            // after the advisory lock wait (unlike a BEFORE trigger's statement snapshot).
            DB::unprepared("CREATE OR REPLACE FUNCTION enforce_organisation_role_name_scope() RETURNS trigger AS \$\$ BEGIN PERFORM pg_advisory_xact_lock(hashtext(NEW.name)); IF EXISTS (SELECT 1 FROM organisation_roles existing WHERE existing.id IS DISTINCT FROM NEW.id AND existing.name = NEW.name AND (existing.organisation_unit_id IS NULL OR NEW.organisation_unit_id IS NULL)) THEN RAISE EXCEPTION 'organisation role name conflicts across scopes'; END IF; RETURN NULL; END; \$\$ LANGUAGE plpgsql");
            DB::unprepared('DROP TRIGGER IF EXISTS organisation_roles_name_scope ON organisation_roles');
            DB::unprepared('CREATE CONSTRAINT TRIGGER organisation_roles_name_scope AFTER INSERT OR UPDATE OF name, organisation_unit_id ON organisation_roles DEFERRABLE INITIALLY DEFERRED FOR EACH ROW EXECUTE FUNCTION enforce_organisation_role_name_scope()');
        } elseif ($driver !== 'sqlite') {
            throw new RuntimeException("Role-name uniqueness is not configured for database driver [{$driver}].");
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            // The legacy sentinel cannot represent NULL and zero as distinct scopes;
            // refuse rollback rather than silently lose/conflict data.
            $conflict = DB::selectOne("SELECT a.id FROM organisation_roles a JOIN organisation_roles b ON a.id < b.id AND a.name = b.name AND (a.organisation_unit_id IS NULL OR b.organisation_unit_id IS NULL OR a.organisation_unit_id = b.organisation_unit_id OR (a.organisation_unit_id IS NULL AND b.organisation_unit_id = 0) OR (b.organisation_unit_id IS NULL AND a.organisation_unit_id = 0)) LIMIT 1");
            if ($conflict) {
                throw new RuntimeException('Cannot roll back role-name uniqueness: global/scoped conflicts exist.');
            }
            DB::unprepared('DROP TRIGGER IF EXISTS organisation_roles_name_scope_insert');
            DB::unprepared('DROP TRIGGER IF EXISTS organisation_roles_name_scope_update');
            DB::statement('DROP INDEX organisation_roles_scope_name_unique ON organisation_roles');
            DB::statement('ALTER TABLE organisation_roles ADD COLUMN organisation_unit_scope BIGINT UNSIGNED GENERATED ALWAYS AS (COALESCE(organisation_unit_id, 0)) STORED');
            DB::statement('CREATE UNIQUE INDEX organisation_roles_scope_name_unique ON organisation_roles (organisation_unit_scope, name)');
            DB::statement('DROP TABLE organisation_role_name_locks');
            DB::unprepared("CREATE TRIGGER organisation_roles_name_scope_insert BEFORE INSERT ON organisation_roles FOR EACH ROW BEGIN IF EXISTS (SELECT 1 FROM organisation_roles existing WHERE existing.name = NEW.name AND (existing.organisation_unit_id IS NULL OR NEW.organisation_unit_id IS NULL)) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'organisation role name conflicts across scopes'; END IF; END");
            DB::unprepared("CREATE TRIGGER organisation_roles_name_scope_update BEFORE UPDATE ON organisation_roles FOR EACH ROW BEGIN IF EXISTS (SELECT 1 FROM organisation_roles existing WHERE existing.id <> OLD.id AND existing.name = NEW.name AND (existing.organisation_unit_id IS NULL OR NEW.organisation_unit_id IS NULL)) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'organisation role name conflicts across scopes'; END IF; END");
        } elseif ($driver === 'pgsql') {
            DB::statement('DROP TRIGGER IF EXISTS organisation_roles_name_scope ON organisation_roles');
            DB::statement('DROP TRIGGER IF EXISTS organisation_roles_require_serializable ON organisation_roles');
            DB::unprepared('DROP FUNCTION IF EXISTS require_serializable_role_name_writes()');
            // Restore the pre-correction trigger function from migration 140000.
            DB::unprepared("CREATE OR REPLACE FUNCTION enforce_organisation_role_name_scope() RETURNS trigger AS \$\$ BEGIN IF EXISTS (SELECT 1 FROM organisation_roles existing WHERE existing.id IS DISTINCT FROM NEW.id AND existing.name = NEW.name AND (existing.organisation_unit_id IS NULL OR NEW.organisation_unit_id IS NULL)) THEN RAISE EXCEPTION 'organisation role name conflicts across scopes'; END IF; RETURN NEW; END; \$\$ LANGUAGE plpgsql");
            DB::statement('CREATE TRIGGER organisation_roles_name_scope BEFORE INSERT OR UPDATE OF name, organisation_unit_id ON organisation_roles FOR EACH ROW EXECUTE FUNCTION enforce_organisation_role_name_scope()');
        } elseif ($driver !== 'sqlite') {
            throw new RuntimeException("Role-name uniqueness is not configured for database driver [{$driver}].");
        }
    }
};
