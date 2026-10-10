<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // The preceding migration installs the cross-scope trigger invariant.
        // Keep this migration as a historical boundary; a COALESCE unique key
        // would incorrectly forbid equal names in two distinct organisations.
    }

    public function down(): void
    {
        // No schema changes are made by up().
    }
};
