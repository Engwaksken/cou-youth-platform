<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'courses',
        'events',
        'contents',
        'life_groups',
        'church_locations',
        'donation_campaigns',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                if (! Schema::hasColumn($tableName, 'visual_type')) {
                    $table->string('visual_type', 20)->default('icon');
                }
                if (! Schema::hasColumn($tableName, 'icon_class')) {
                    $table->string('icon_class', 100)->nullable();
                }
                if (! Schema::hasColumn($tableName, 'image_path')) {
                    $table->string('image_path')->nullable();
                }
                if (! Schema::hasColumn($tableName, 'banner_path')) {
                    $table->string('banner_path')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            $columns = collect(['visual_type', 'icon_class', 'image_path', 'banner_path'])
                ->filter(fn (string $column): bool => Schema::hasColumn($tableName, $column))
                ->values()
                ->all();

            if ($columns) {
                Schema::table($tableName, function (Blueprint $table) use ($columns): void {
                    $table->dropColumn($columns);
                });
            }
        }
    }
};
