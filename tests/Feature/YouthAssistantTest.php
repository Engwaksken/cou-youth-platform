<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Services\AI\AiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class YouthAssistantTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_web_youth_assistant(): void
    {
        $this->post('/youth-assistant', ['message' => 'Find an event'])
            ->assertRedirect('/login');
    }

    public function test_authenticated_youth_assistant_adds_platform_safety_context(): void
    {
        $user = User::factory()->create();

        $this->mock(AiService::class, function (MockInterface $mock) use ($user): void {
            $mock->shouldReceive('ask')
                ->once()
                ->withArgs(function (string $prompt, string $module, ?int $userId) use ($user): bool {
                    return $module === 'chatbot'
                        && $userId === $user->id
                        && str_contains($prompt, 'Church of Uganda Youth Platform Youth Assistant')
                        && str_contains($prompt, 'Accessibility and inclusion rules')
                        && str_contains($prompt, 'Safety and safeguarding rules')
                        && str_contains($prompt, 'Find an event');
                })
                ->andReturn('Open the Events section to see upcoming youth events.');
        });

        $this->actingAs($user)
            ->postJson('/youth-assistant', ['message' => 'Find an event'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath(
                'data.reply',
                'Open the Events section to see upcoming youth events.'
            );
    }
}
