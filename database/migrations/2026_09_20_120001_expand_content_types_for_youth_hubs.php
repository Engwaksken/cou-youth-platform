<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('contents') || ! Schema::hasColumn('contents', 'type')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE `contents` MODIFY `type` ENUM('news','announcement','devotion','bible_study','resource','opportunity','mission','talent','youth_business') NOT NULL"
            );
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('contents') || ! Schema::hasColumn('contents', 'type')) {
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $unsupported = DB::table('contents')
            ->whereIn('type', ['mission', 'talent', 'youth_business'])
            ->exists();

        if ($unsupported) {
            throw new RuntimeException(
                'Cannot roll back content type expansion while mission, talent or youth_business content exists.'
            );
        }

        DB::statement(
            "ALTER TABLE `contents` MODIFY `type` ENUM('news','announcement','devotion','bible_study','resource','opportunity') NOT NULL"
        );
    }
};
