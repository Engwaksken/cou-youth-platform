<?php

declare(strict_types=1);

namespace Tests\Feature\AI;

use App\Models\AiSetting;
use App\Models\AiUsageLog;
use App\Services\AI\AiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class AiServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_openai_response_is_returned_and_logged(): void
    {
        AiSetting::create([
            'provider' => 'openai',
            'model' => 'test-model',
            'api_key' => 'secret-test-key',
            'api_endpoint' => 'https://api.example.test/v1',
            'temperature' => 0.2,
            'max_tokens' => 500,
            'timeout_seconds' => 30,
            'retry_count' => 0,
            'is_enabled' => true,
        ]);

        Http::fake([
            'https://api.example.test/v1/responses' => Http::response([
                'output' => [
                    [
                        'content' => [
                            ['text' => 'The AI connection is working.'],
                        ],
                    ],
                ],
                'usage' => [
                    'input_tokens' => 10,
                    'output_tokens' => 6,
                ],
            ], 200),
        ]);

        $reply = app(AiService::class)->ask('Hello', 'chatbot');

        $this->assertSame('The AI connection is working.', $reply);
        $this->assertDatabaseHas('ai_usage_logs', [
            'module' => 'chatbot',
            'provider' => 'openai',
            'model' => 'test-model',
            'successful' => true,
            'input_tokens' => 10,
            'output_tokens' => 6,
        ]);
    }

    public function test_daily_limit_prevents_an_external_request(): void
    {
        AiSetting::create([
            'provider' => 'openai',
            'model' => 'test-model',
            'api_key' => 'secret-test-key',
            'api_endpoint' => 'https://api.example.test/v1',
            'temperature' => 0.2,
            'max_tokens' => 500,
            'daily_limit' => 1,
            'timeout_seconds' => 30,
            'retry_count' => 0,
            'is_enabled' => true,
        ]);

        AiUsageLog::create([
            'module' => 'chatbot',
            'provider' => 'openai',
            'model' => 'test-model',
            'successful' => true,
        ]);

        Http::fake();

        try {
            app(AiService::class)->ask('This should be blocked', 'chatbot');
            $this->fail('Expected the daily usage limit to block the request.');
        } catch (RuntimeException $exception) {
            $this->assertSame('AI daily usage limit reached.', $exception->getMessage());
        }

        Http::assertNothingSent();
        $this->assertDatabaseHas('ai_usage_logs', [
            'module' => 'chatbot',
            'successful' => false,
            'error_code' => 'RuntimeException',
        ]);
    }
}
