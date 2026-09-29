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
        $this->createSetting([
            'provider' => 'openai',
            'model' => 'test-model',
            'api_endpoint' => 'https://api.example.test/v1',
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

    public function test_deepseek_uses_chat_completions_and_returns_text(): void
    {
        $this->createSetting([
            'provider' => 'deepseek',
            'model' => 'deepseek-chat',
            'api_endpoint' => 'https://api.deepseek.example',
        ]);

        Http::fake([
            'https://api.deepseek.example/chat/completions' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'DeepSeek connection working.']],
                ],
                'usage' => [
                    'prompt_tokens' => 7,
                    'completion_tokens' => 4,
                ],
            ], 200),
        ]);

        $reply = app(AiService::class)->testConnection();

        $this->assertSame('DeepSeek connection working.', $reply);

        Http::assertSent(fn ($request) =>
            $request->url() === 'https://api.deepseek.example/chat/completions'
            && $request['model'] === 'deepseek-chat'
        );
    }

    public function test_gemini_uses_generate_content_and_returns_text(): void
    {
        $this->createSetting([
            'provider' => 'gemini',
            'model' => 'gemini-test-model',
            'api_endpoint' => 'https://gemini.example/v1beta',
        ]);

        Http::fake([
            'https://gemini.example/v1beta/models/gemini-test-model:generateContent' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Gemini connection working.'],
                            ],
                        ],
                    ],
                ],
                'usageMetadata' => [
                    'promptTokenCount' => 8,
                    'candidatesTokenCount' => 5,
                ],
            ], 200),
        ]);

        $reply = app(AiService::class)->testConnection();

        $this->assertSame('Gemini connection working.', $reply);

        Http::assertSent(fn ($request) =>
            $request->url() === 'https://gemini.example/v1beta/models/gemini-test-model:generateContent'
            && $request->hasHeader('x-goog-api-key', 'secret-test-key')
        );
    }

    public function test_provider_401_returns_actionable_api_key_error(): void
    {
        $this->createSetting([
            'provider' => 'openai',
            'model' => 'test-model',
            'api_endpoint' => 'https://api.example.test/v1',
        ]);

        Http::fake([
            'https://api.example.test/v1/responses' => Http::response([
                'error' => [
                    'message' => 'Incorrect API key provided.',
                ],
            ], 401),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The AI provider rejected the API key.');

        app(AiService::class)->testConnection();
    }

    public function test_connection_test_bypasses_usage_limits(): void
    {
        $this->createSetting([
            'provider' => 'openai',
            'model' => 'test-model',
            'api_endpoint' => 'https://api.example.test/v1',
            'daily_limit' => 1,
        ]);

        AiUsageLog::create([
            'module' => 'chatbot',
            'provider' => 'openai',
            'model' => 'test-model',
            'successful' => true,
        ]);

        Http::fake([
            'https://api.example.test/v1/responses' => Http::response([
                'output_text' => 'AI connection working',
            ], 200),
        ]);

        $reply = app(AiService::class)->testConnection();

        $this->assertSame('AI connection working', $reply);
        Http::assertSentCount(1);
    }

    public function test_gpt_5_request_does_not_send_temperature(): void
    {
        $this->createSetting([
            'provider' => 'openai',
            'model' => 'gpt-5-test',
            'api_endpoint' => 'https://api.example.test/v1',
        ]);

        Http::fake([
            'https://api.example.test/v1/responses' => Http::response([
                'output_text' => 'OK',
            ], 200),
        ]);

        app(AiService::class)->testConnection();

        Http::assertSent(fn ($request) => ! array_key_exists('temperature', $request->data()));
    }

    public function test_daily_limit_prevents_an_external_request(): void
    {
        $this->createSetting([
            'provider' => 'openai',
            'model' => 'test-model',
            'api_endpoint' => 'https://api.example.test/v1',
            'daily_limit' => 1,
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

    /** @param array<string, mixed> $overrides */
    private function createSetting(array $overrides = []): AiSetting
    {
        return AiSetting::create(array_merge([
            'provider' => 'openai',
            'model' => 'test-model',
            'api_key' => 'secret-test-key',
            'api_endpoint' => 'https://api.example.test/v1',
            'temperature' => 0.2,
            'max_tokens' => 500,
            'daily_limit' => 0,
            'monthly_limit' => 0,
            'per_user_daily_limit' => 0,
            'timeout_seconds' => 30,
            'retry_count' => 0,
            'is_enabled' => true,
        ], $overrides));
    }
}
