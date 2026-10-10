<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if (! in_array($driver, ['sqlite', 'pgsql', 'mysql', 'mariadb'], true)) {
            throw new RuntimeException("Role-name uniqueness is not configured for database driver [{$driver}].");
        }

        // Enforce cross-scope collisions with triggers: a global role name
        // conflicts with every scoped role of that name, while two scoped
        // roles may share a name only when their organisation differs.
        if ($driver === 'sqlite') {
            // Avoid a name-only global index: the scope trigger enforces global
            // uniqueness without making the index appear to cover scoped rows.
            DB::statement('DROP INDEX IF EXISTS organisation_roles_global_name_unique');
            DB::unprepared("CREATE TRIGGER organisation_roles_name_scope_insert BEFORE INSERT ON organisation_roles WHEN EXISTS (SELECT 1 FROM organisation_roles existing WHERE existing.name = NEW.name AND (existing.organisation_unit_id IS NULL OR NEW.organisation_unit_id IS NULL)) BEGIN SELECT RAISE(ABORT, 'organisation role name conflicts across scopes'); END");
            DB::unprepared("CREATE TRIGGER organisation_roles_name_scope_update BEFORE UPDATE OF name, organisation_unit_id ON organisation_roles WHEN EXISTS (SELECT 1 FROM organisation_roles existing WHERE existing.id <> OLD.id AND existing.name = NEW.name AND (existing.organisation_unit_id IS NULL OR NEW.organisation_unit_id IS NULL)) BEGIN SELECT RAISE(ABORT, 'organisation role name conflicts across scopes'); END");
        } elseif ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS organisation_roles_global_name_unique');
            DB::unprepared("CREATE FUNCTION enforce_organisation_role_name_scope() RETURNS trigger AS \$\$ BEGIN IF EXISTS (SELECT 1 FROM organisation_roles existing WHERE existing.id IS DISTINCT FROM NEW.id AND existing.name = NEW.name AND (existing.organisation_unit_id IS NULL OR NEW.organisation_unit_id IS NULL)) THEN RAISE EXCEPTION 'organisation role name conflicts across scopes'; END IF; RETURN NEW; END; \$\$ LANGUAGE plpgsql");
            DB::statement('CREATE TRIGGER organisation_roles_name_scope BEFORE INSERT OR UPDATE OF name, organisation_unit_id ON organisation_roles FOR EACH ROW EXECUTE FUNCTION enforce_organisation_role_name_scope()');
        } else {
            DB::unprepared("CREATE TRIGGER organisation_roles_name_scope_insert BEFORE INSERT ON organisation_roles FOR EACH ROW BEGIN IF EXISTS (SELECT 1 FROM organisation_roles existing WHERE existing.name = NEW.name AND (existing.organisation_unit_id IS NULL OR NEW.organisation_unit_id IS NULL)) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'organisation role name conflicts across scopes'; END IF; END");
            DB::unprepared("CREATE TRIGGER organisation_roles_name_scope_update BEFORE UPDATE ON organisation_roles FOR EACH ROW BEGIN IF EXISTS (SELECT 1 FROM organisation_roles existing WHERE existing.id <> OLD.id AND existing.name = NEW.name AND (existing.organisation_unit_id IS NULL OR NEW.organisation_unit_id IS NULL)) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'organisation role name conflicts across scopes'; END IF; END");
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if (! in_array($driver, ['sqlite', 'pgsql', 'mysql', 'mariadb'], true)) {
            throw new RuntimeException("Role-name uniqueness is not configured for database driver [{$driver}].");
        }

        if ($driver === 'sqlite') {
            DB::unprepared('DROP TRIGGER IF EXISTS organisation_roles_name_scope_insert');
            DB::unprepared('DROP TRIGGER IF EXISTS organisation_roles_name_scope_update');
            DB::statement('CREATE UNIQUE INDEX organisation_roles_global_name_unique ON organisation_roles (name) WHERE organisation_unit_id IS NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('DROP TRIGGER IF EXISTS organisation_roles_name_scope ON organisation_roles');
            DB::statement('DROP FUNCTION IF EXISTS enforce_organisation_role_name_scope()');
            DB::statement('CREATE UNIQUE INDEX organisation_roles_global_name_unique ON organisation_roles (name) WHERE organisation_unit_id IS NULL');
        } else {
            DB::unprepared('DROP TRIGGER IF EXISTS organisation_roles_name_scope_insert');
            DB::unprepared('DROP TRIGGER IF EXISTS organisation_roles_name_scope_update');
        }
    }
};
