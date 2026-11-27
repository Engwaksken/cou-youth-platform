<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\OrganisationUnit;
use App\Models\User;
use App\Models\UserOrganisationRole;
use App\Models\YouthProfile;
use App\Support\ChurchOfUgandaDioceses;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class YouthDioceseTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        UserOrganisationRole::query()->create(['user_id' => $user->id, 'role' => 'super_admin', 'is_active' => true]);

        return $user;
    }

    private function youthIn(int $dioceseId, string $name): User
    {
        $user = User::factory()->create(['name' => $name]);
        YouthProfile::create([
            'user_id' => $user->id,
            'date_of_birth' => now()->subYears(20)->toDateString(),
            'age_category' => 'youth',
            'diocese_id' => $dioceseId,
        ]);

        return $user;
    }

    private function donation(User $user, int $dioceseId, float $amount): Donation
    {
        return Donation::create([
            'reference' => (string) Str::uuid(),
            'user_id' => $user->id,
            'diocese_id' => $dioceseId,
            'amount' => $amount,
            'currency' => 'UGX',
            'status' => 'successful',
            'donor_name' => $user->name,
            'paid_at' => now(),
        ]);
    }

    public function test_diocese_seeding_creates_every_diocese_once(): void
    {
        ChurchOfUgandaDioceses::seed();
        ChurchOfUgandaDioceses::seed();

        $this->assertSame(
            count(ChurchOfUgandaDioceses::NAMES),
            OrganisationUnit::query()->where('type', 'diocese')->count(),
        );
        $this->assertNotSame(0, $this->dioceseId('Kampala Diocese'));
    }

    public function test_registration_page_lists_dioceses(): void
    {
        $this->dioceseId();

        $this->get('/signup')->assertOk()->assertSee('Church of Uganda diocese')->assertSee('Namirembe Diocese');
    }

    public function test_registration_requires_a_valid_diocese(): void
    {
        $payload = [
            'name' => 'Youth Member',
            'email' => 'youth@example.com',
            'date_of_birth' => now()->subYears(20)->toDateString(),
            'password' => 'StrongPass123',
            'password_confirmation' => 'StrongPass123',
        ];

        $this->post('/signup', $payload)->assertSessionHasErrors('diocese_id');

        $parish = OrganisationUnit::create(['type' => 'parish', 'name' => 'Some Parish']);
        $this->post('/signup', [...$payload, 'diocese_id' => $parish->id])->assertSessionHasErrors('diocese_id');
        $this->assertGuest();

        $this->post('/signup', [...$payload, 'diocese_id' => $this->dioceseId()])->assertRedirect(route('youth.dashboard'));
        $this->assertDatabaseHas('youth_profiles', ['diocese_id' => $this->dioceseId()]);
    }

    public function test_signup_checks_that_passwords_match(): void
    {
        $this->get('/signup')
            ->assertOk()
            ->assertSee('id="passwordMatchHint"', false)
            ->assertSee('checkPasswordMatch', false);

        $this->post('/signup', [
            'name' => 'Youth Member',
            'email' => 'youth@example.com',
            'date_of_birth' => now()->subYears(20)->toDateString(),
            'diocese_id' => $this->dioceseId(),
            'password' => 'StrongPass123',
            'password_confirmation' => 'StrongPass124',
        ])->assertSessionHasErrors('password');

        $this->assertGuest();
    }

    public function test_admin_can_filter_users_by_diocese(): void
    {
        $this->youthIn($this->dioceseId('Namirembe Diocese'), 'Namirembe Youth');
        $this->youthIn($this->dioceseId('Kigezi Diocese'), 'Kigezi Youth');

        $this->actingAs($this->superAdmin())
            ->get('/admin/users?diocese_id='.$this->dioceseId('Kigezi Diocese'))
            ->assertOk()
            ->assertSee('Kigezi Youth')
            ->assertDontSee('Namirembe Youth');
    }

    public function test_donation_reports_filter_by_diocese(): void
    {
        $namirembe = $this->dioceseId('Namirembe Diocese');
        $kigezi = $this->dioceseId('Kigezi Diocese');
        $this->donation($this->youthIn($namirembe, 'Namirembe Youth'), $namirembe, 15000);
        $this->donation($this->youthIn($kigezi, 'Kigezi Youth'), $kigezi, 7000);

        $admin = $this->superAdmin();

        $this->actingAs($admin)->get('/admin/donations?diocese_id='.$kigezi)
            ->assertOk()
            ->assertSee('UGX 7,000')
            ->assertDontSee('UGX 22,000')
            ->assertSee('By Diocese');

        $this->actingAs($admin)->get('/admin/reports?diocese_id='.$namirembe)
            ->assertOk()
            ->assertSee('Namirembe Diocese')
            ->assertSee('15,000');

        $this->actingAs($admin)->get('/admin/financial-reports?diocese_id='.$kigezi)
            ->assertOk()
            ->assertSee('Income by diocese')
            ->assertSee('UGX 7,000');
    }
}
