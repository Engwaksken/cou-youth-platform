<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('platform_notifications')) {
            Schema::create('platform_notifications', function (Blueprint $table) {
                $table->id();

                $table->string('title');
                $table->text('body');

                $table->enum('channel', [
                    'in_app',
                    'push',
                    'email',
                    'all',
                ])->default('in_app');

                $table->foreignId('organisation_unit_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();

                $table->enum('age_category', [
                    'teen',
                    'youth',
                    'young_adult',
                    'all',
                ])->default('all');

                $table->string('action_url')->nullable();

                $table->timestamp('scheduled_at')->nullable();
                $table->timestamp('sent_at')->nullable();

                $table->foreignId('created_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->boolean('is_active')->default(true);

                $table->timestamps();

                $table->index(
                    'organisation_unit_id',
                    'pn_org_unit_idx'
                );

                $table->index(
                    'scheduled_at',
                    'pn_scheduled_idx'
                );

                $table->index(
                    'sent_at',
                    'pn_sent_idx'
                );

                $table->index(
                    ['is_active', 'scheduled_at'],
                    'pn_active_schedule_idx'
                );
            });
        }

        if (! Schema::hasTable('platform_notification_receipts')) {
            Schema::create('platform_notification_receipts', function (Blueprint $table) {
                $table->id();

                $table->foreignId('platform_notification_id')
                    ->constrained('platform_notifications')
                    ->cascadeOnDelete();

                $table->foreignId('user_id')
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('read_at')->nullable();

                $table->timestamps();

                /*
                 * Use a short explicit name because MySQL limits
                 * identifiers such as index names to 64 characters.
                 */
                $table->unique(
                    [
                        'platform_notification_id',
                        'user_id',
                    ],
                    'pn_receipt_user_unique'
                );

                $table->index(
                    'user_id',
                    'pn_receipt_user_idx'
                );

                $table->index(
                    'read_at',
                    'pn_receipt_read_idx'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_notification_receipts');
        Schema::dropIfExists('platform_notifications');
    }
};