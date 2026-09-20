<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class YouthHubContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_api_filters_published_mission_content(): void
    {
        Content::create([
            'type' => 'mission',
            'title' => 'Community Mission',
            'slug' => 'community-mission',
            'body' => 'Youth mission outreach.',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);

        Content::create([
            'type' => 'talent',
            'title' => 'Youth Music Talent',
            'slug' => 'youth-music-talent',
            'body' => 'Talent profile.',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);

        $this->getJson('/api/v1/content?type=mission')
            ->assertOk()
            ->assertJsonPath('data.0.type', 'mission')
            ->assertJsonPath('data.0.title', 'Community Mission')
            ->assertJsonMissing(['title' => 'Youth Music Talent']);
    }

    public function test_public_api_exposes_published_youth_business_content(): void
    {
        Content::create([
            'type' => 'youth_business',
            'title' => 'Youth Enterprise',
            'slug' => 'youth-enterprise',
            'summary' => 'A youth-led business.',
            'body' => 'Business profile.',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);

        $this->getJson('/api/v1/content?type=youth_business')
            ->assertOk()
            ->assertJsonPath('data.0.type', 'youth_business')
            ->assertJsonPath('data.0.title', 'Youth Enterprise');
    }

    public function test_draft_youth_hub_content_is_not_public(): void
    {
        Content::create([
            'type' => 'talent',
            'title' => 'Draft Talent',
            'slug' => 'draft-talent',
            'body' => 'Not ready.',
            'status' => 'draft',
        ]);

        $this->getJson('/api/v1/content?type=talent')
            ->assertOk()
            ->assertJsonMissing(['title' => 'Draft Talent']);
    }
}
