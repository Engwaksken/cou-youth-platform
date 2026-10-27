<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use App\Models\UserOrganisationRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FaviconTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $admin = User::factory()->create();
        UserOrganisationRole::query()->create([
            'user_id' => $admin->id, 'role' => 'super_admin', 'is_active' => true,
        ]);

        return $admin;
    }

    public function test_uploaded_favicon_is_linked_on_every_layout_and_served(): void
    {
        Storage::fake('public');

        $this->actingAs($this->superAdmin())
            ->put(route('admin.settings.update'), ['favicon' => UploadedFile::fake()->createWithContent('icon.png', base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
            ))])
            ->assertSessionHasNoErrors();

        $path = SiteSetting::get('favicon');
        Storage::disk('public')->assertExists($path);
        $href = e(route('pwa.favicon', ['v' => substr(sha1($path), 0, 10)]));

        $this->get(route('home'))->assertSee('<link rel="icon" href="'.$href.'">', false);
        $this->get(route('admin.dashboard'))->assertSee('<link rel="icon" href="'.$href.'">', false);

        $this->get(route('pwa.favicon'))->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_no_favicon_link_or_file_without_an_upload(): void
    {
        Storage::fake('public');

        $this->get(route('home'))->assertDontSee('rel="icon"', false);
        $this->get(route('pwa.favicon'))->assertNotFound();
    }

    public function test_favicon_upload_rejects_non_image_files(): void
    {
        Storage::fake('public');

        $this->actingAs($this->superAdmin())
            ->put(route('admin.settings.update'), ['favicon' => UploadedFile::fake()->create('icon.html', 1, 'text/html')])
            ->assertSessionHasErrors('favicon');

        $this->assertNull(SiteSetting::get('favicon'));
    }
}
