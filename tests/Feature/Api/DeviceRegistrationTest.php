<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\PlatformNotification;
use App\Models\PlatformNotificationReceipt;
use App\Models\NotificationPreference;
use App\Services\Notifications\NotificationDeliveryService;
use App\Services\Branding\BrandingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class DeviceRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_preserves_a_4096_character_token_and_stores_its_sha256_without_leaking_it(): void
    {
        $user = User::factory()->create();
        $token = str_repeat('x', 4096);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/devices', [
            'platform' => 'ios', 'push_token' => $token,
        ]);

        $response->assertSuccessful()->assertDontSee($token)->assertDontSee(hash('sha256', $token));
        $stored = DB::table('user_devices')->where('user_id', $user->id)->first();
        $this->assertSame($token, Crypt::decryptString($stored->push_token));
        $this->assertSame(hash('sha256', $token), $stored->push_token_hash);
    }

    public function test_registering_the_same_token_twice_for_one_user_upserts_one_row(): void
    {
        $user = User::factory()->create();
        $data = ['platform' => 'android', 'push_token' => 'same-token'];

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/devices', $data)->assertSuccessful();
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/devices', $data)->assertSuccessful();

        $this->assertSame(1, DB::table('user_devices')->where('user_id', $user->id)->count());
    }

    public function test_same_token_can_be_registered_by_different_users(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();
        $data = ['platform' => 'android', 'push_token' => 'shared-token'];

        $this->actingAs($first, 'sanctum')->postJson('/api/v1/devices', $data)->assertSuccessful();
        $this->actingAs($second, 'sanctum')->postJson('/api/v1/devices', $data)->assertSuccessful();

        $this->assertSame(2, DB::table('user_devices')->where('push_token_hash', hash('sha256', 'shared-token'))->count());
    }

    public function test_registration_requires_authentication(): void
    {
        $this->postJson('/api/v1/devices', ['platform' => 'ios', 'push_token' => 'token'])->assertUnauthorized();
    }

    public function test_delete_removes_a_registration_by_full_token(): void
    {
        $user = User::factory()->create();
        $token = 'delete-me';
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/devices', ['platform' => 'ios', 'push_token' => $token]);

        $this->actingAs($user, 'sanctum')->deleteJson('/api/v1/devices', ['push_token' => $token])->assertSuccessful();

        $this->assertDatabaseMissing('user_devices', ['user_id' => $user->id, 'push_token' => $token]);
    }

    public function test_migration_upgrades_original_schema_and_is_resumable(): void
    {
        $user = User::factory()->create();
        $token = 'legacy-token';
        Schema::drop('user_devices');
        Schema::create('user_devices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('platform', 20);
            $table->string('device_name')->nullable();
            $table->text('push_token')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
            // MySQL can only uniquely index a TEXT column by prefix, so legacy MySQL schemas used one.
            if (DB::getDriverName() !== 'mysql') {
                $table->unique(['user_id', 'push_token']);
            }
        });
        if (DB::getDriverName() === 'mysql') {
            DB::statement('CREATE UNIQUE INDEX user_devices_user_id_push_token_unique ON user_devices (user_id, push_token(191))');
        }
        $id = DB::table('user_devices')->insertGetId([
            'user_id' => $user->id, 'platform' => 'ios', 'push_token' => $token,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $migration = require database_path('migrations/2026_10_10_000001_add_push_token_hash_to_user_devices.php');
        $migration->up();
        $migration->up();

        $this->assertSame(hash('sha256', $token), DB::table('user_devices')->where('id', $id)->value('push_token_hash'));
        $this->assertSame($token, Crypt::decryptString(DB::table('user_devices')->where('id', $id)->value('push_token')));
        $this->assertSame(1, DB::table('user_devices')->count());
    }

    public function test_duplicate_legacy_tokens_abort_before_schema_or_data_mutation(): void
    {
        $user = User::factory()->create();
        Schema::drop('user_devices');
        Schema::create('user_devices', function (Blueprint $table): void {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('platform', 20); $table->text('push_token')->nullable(); $table->timestamps();
        });
        foreach ([1, 2] as $_) {
            DB::table('user_devices')->insert(['user_id' => $user->id, 'platform' => 'ios', 'push_token' => 'duplicate', 'created_at' => now(), 'updated_at' => now()]);
        }
        $migration = require database_path('migrations/2026_10_10_000001_add_push_token_hash_to_user_devices.php');
        try { $migration->up(); $this->fail('Expected duplicate conflict.'); }
        catch (\RuntimeException $exception) { $this->assertStringContainsString('Duplicate push tokens', $exception->getMessage()); }
        $this->assertFalse(Schema::hasColumn('user_devices', 'push_token_hash'));
        $this->assertSame(['duplicate', 'duplicate'], DB::table('user_devices')->orderBy('id')->pluck('push_token')->all());
    }

    public function test_malformed_ciphertext_aborts_migration_without_schema_or_data_mutation(): void
    {
        $user = User::factory()->create();
        $malformedCiphertext = base64_encode(json_encode(['iv' => 'invalid', 'value' => 'broken', 'mac' => 'invalid']));
        Schema::drop('user_devices');
        Schema::create('user_devices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('platform', 20);
            $table->text('push_token')->nullable();
            $table->timestamps();
        });
        $id = DB::table('user_devices')->insertGetId([
            'user_id' => $user->id,
            'platform' => 'ios',
            'push_token' => $malformedCiphertext,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $migration = require database_path('migrations/2026_10_10_000001_add_push_token_hash_to_user_devices.php');

        try {
            $migration->up();
            $this->fail('Expected malformed ciphertext to abort migration.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('malformed or undecryptable ciphertext', $exception->getMessage());
        }

        $this->assertFalse(Schema::hasColumn('user_devices', 'push_token_hash'));
        $this->assertSame($malformedCiphertext, DB::table('user_devices')->where('id', $id)->value('push_token'));
    }

    public function test_rollback_decrypts_values_and_retains_hash_column(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/devices', ['platform' => 'ios', 'push_token' => 'rollback-token'])->assertSuccessful();
        $migration = require database_path('migrations/2026_10_10_000001_add_push_token_hash_to_user_devices.php');
        $migration->down();
        $this->assertSame('rollback-token', DB::table('user_devices')->value('push_token'));
        $this->assertTrue(Schema::hasColumn('user_devices', 'push_token_hash'));
    }

    public function test_push_delivery_decrypts_token_before_sending_and_database_has_no_plaintext(): void
    {
        Http::fake();
        config(['services.cou_push.bridge_url' => 'https://push.example.test/send', 'services.cou_push.secret' => 'secret']);
        $user = User::factory()->create();
        $token = 'delivery-token';
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/devices', ['platform' => 'ios', 'push_token' => $token])->assertSuccessful();
        $this->assertDatabaseMissing('user_devices', ['push_token' => $token]);
        $notification = PlatformNotification::create(['title' => 'Test', 'body' => 'Body', 'channel' => 'push', 'is_active' => true]);
        $receipt = PlatformNotificationReceipt::create(['platform_notification_id' => $notification->id, 'user_id' => $user->id]);
        app(NotificationDeliveryService::class)->deliver($notification, $receipt);
        $this->assertTrue(NotificationPreference::where('user_id', $user->id)->firstOrFail()->push);
        Http::assertSent(fn ($request) => $request['token'] === $token);
    }

    public function test_push_delivery_respects_an_existing_push_opt_out(): void
    {
        Http::fake();
        config(['services.cou_push.bridge_url' => 'https://push.example.test/send', 'services.cou_push.secret' => 'secret']);
        $user = User::factory()->create();
        NotificationPreference::create(['user_id' => $user->id, 'push' => false]);
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/devices', ['platform' => 'ios', 'push_token' => 'opted-out-token'])->assertSuccessful();
        $notification = PlatformNotification::create(['title' => 'Test', 'body' => 'Body', 'channel' => 'push', 'is_active' => true]);
        $receipt = PlatformNotificationReceipt::create(['platform_notification_id' => $notification->id, 'user_id' => $user->id]);

        app(NotificationDeliveryService::class)->deliver($notification, $receipt);

        $this->assertFalse(NotificationPreference::where('user_id', $user->id)->firstOrFail()->push);
        Http::assertNothingSent();
    }

    public function test_push_delivery_skips_malformed_ciphertext_and_attempts_valid_device(): void
    {
        Http::fake();
        config(['services.cou_push.bridge_url' => 'https://push.example.test/send', 'services.cou_push.secret' => 'secret']);
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/devices', ['platform' => 'ios', 'push_token' => 'valid-delivery-token'])->assertSuccessful();

        DB::table('user_devices')->insert([
            'user_id' => $user->id,
            'platform' => 'android',
            'push_token' => base64_encode(json_encode(['iv' => 'invalid', 'value' => 'broken', 'mac' => 'invalid'])),
            'push_token_hash' => hash('sha256', 'malformed-placeholder'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $notification = PlatformNotification::create(['title' => 'Test', 'body' => 'Body', 'channel' => 'push', 'is_active' => true]);
        $receipt = PlatformNotificationReceipt::create(['platform_notification_id' => $notification->id, 'user_id' => $user->id]);

        app(NotificationDeliveryService::class)->deliver($notification, $receipt);

        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request['token'] === 'valid-delivery-token');
    }
}
