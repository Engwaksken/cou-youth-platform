<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\UserOrganisationRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $role = 'super_admin'): User
    {
        $user = User::factory()->create(['password' => bcrypt('secret-password')]);
        UserOrganisationRole::query()->create(['user_id' => $user->id, 'role' => $role, 'is_active' => true]);

        return $user;
    }

    private function logAt(string $when, array $attributes = []): AuditLog
    {
        $log = AuditLog::query()->create(array_merge(['action' => 'put', 'module' => 'admin.events.update'], $attributes));
        $log->forceFill(['created_at' => $when, 'updated_at' => $when])->save();

        return $log;
    }

    public function test_sign_ins_failures_and_sign_outs_are_recorded(): void
    {
        $user = User::factory()->create(['email' => 'youth@example.org', 'password' => bcrypt('secret-password')]);

        $this->post(route('login.attempt'), ['email' => 'youth@example.org', 'password' => 'wrong-password']);
        $this->post(route('login.attempt'), ['email' => 'youth@example.org', 'password' => 'secret-password']);
        $this->post(route('logout'));

        $this->assertDatabaseHas('audit_logs', ['action' => 'login_failed', 'module' => 'auth', 'user_id' => $user->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'login', 'module' => 'auth', 'user_id' => $user->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'logout', 'module' => 'auth', 'user_id' => $user->id]);
        $failed = AuditLog::query()->where('action', 'login_failed')->firstOrFail();
        $this->assertSame('youth@example.org', $failed->after['email']);
        $this->assertStringNotContainsString('wrong-password', json_encode($failed->getAttributes()));
    }

    public function test_admin_sign_in_is_recorded_once(): void
    {
        $admin = $this->admin();

        $this->post(route('admin.login.attempt'), ['email' => $admin->email, 'password' => 'secret-password']);

        $this->assertSame(1, AuditLog::query()->where('user_id', $admin->id)->count());
        $this->assertDatabaseHas('audit_logs', ['user_id' => $admin->id, 'action' => 'login']);
    }

    public function test_super_admin_can_view_and_filter_activity(): void
    {
        $admin = $this->admin();
        $this->logAt(now()->toDateTimeString(), ['user_id' => $admin->id, 'action' => 'delete', 'module' => 'admin.events.destroy']);
        $this->logAt(now()->toDateTimeString(), ['action' => 'login_failed', 'module' => 'auth', 'after' => ['email' => 'intruder@example.org']]);

        $this->actingAs($admin)->get(route('admin.activity-logs.index'))
            ->assertOk()
            ->assertSee('Events › Destroy')
            ->assertSee('intruder@example.org')
            ->assertSee(route('admin.activity-logs.index'), false);

        $this->actingAs($admin)->get(route('admin.activity-logs.index', ['action' => 'login_failed']))
            ->assertOk()
            ->assertSee('intruder@example.org')
            ->assertDontSee('Events › Destroy');
    }

    public function test_activity_logs_are_limited_to_super_admins(): void
    {
        $this->actingAs($this->admin('diocesan_admin'))
            ->get(route('admin.activity-logs.index'))
            ->assertForbidden();

        $this->actingAs($this->admin('diocesan_admin'))
            ->get(route('admin.dashboard'))
            ->assertDontSee(route('admin.activity-logs.index'), false);
    }

    public function test_monthly_archive_moves_old_months_to_compressed_files(): void
    {
        Storage::fake('local');
        $this->travelTo('2026-10-15 09:00:00');
        $user = User::factory()->create(['name' => 'Archived Admin']);

        $this->logAt('2026-05-03 10:00:00', ['user_id' => $user->id]);
        $this->logAt('2026-05-28 10:00:00');
        $this->logAt('2026-06-10 10:00:00');
        $kept = $this->logAt('2026-07-01 00:00:00');

        $this->artisan('audit:archive', ['--months' => 3])->assertSuccessful();

        $this->assertSame([$kept->id], AuditLog::query()->pluck('id')->all());
        Storage::disk('local')->assertExists('audit-archives/audit-logs-2026-05.jsonl.gz');
        Storage::disk('local')->assertExists('audit-archives/audit-logs-2026-06.jsonl.gz');
        Storage::disk('local')->assertMissing('audit-archives/audit-logs-2026-07.jsonl.gz');

        $lines = $this->archiveLines('audit-logs-2026-05.jsonl.gz');
        $this->assertCount(2, $lines);
        $this->assertSame('Archived Admin', $lines[0]['user_name']);

        // A later run for the same month appends instead of overwriting.
        $this->logAt('2026-05-30 10:00:00');
        $this->artisan('audit:archive', ['--months' => 3])->assertSuccessful();
        $this->assertCount(3, $this->archiveLines('audit-logs-2026-05.jsonl.gz'));
    }

    public function test_super_admin_can_download_archives_and_paths_are_locked_down(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('audit-archives/audit-logs-2026-05.jsonl.gz', gzencode("{}\n"));
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.activity-logs.index'))->assertSee('May 2026');
        $this->actingAs($admin)->get(route('admin.activity-logs.archives.download', 'audit-logs-2026-05.jsonl.gz'))->assertOk();
        $this->actingAs($admin)->get('/admin/activity-logs/archives/..%2F.env')->assertNotFound();
        $this->actingAs($this->admin('diocesan_admin'))
            ->get(route('admin.activity-logs.archives.download', 'audit-logs-2026-05.jsonl.gz'))
            ->assertForbidden();
    }

    public function test_archive_is_scheduled_monthly(): void
    {
        $this->artisan('schedule:list')->expectsOutputToContain('audit:archive')->assertSuccessful();
    }

    /** @return list<array<string, mixed>> */
    private function archiveLines(string $file): array
    {
        $raw = gzdecode(Storage::disk('local')->get('audit-archives/'.$file));

        return array_map(fn (string $line) => json_decode($line, true), array_values(array_filter(explode("\n", $raw))));
    }
}
