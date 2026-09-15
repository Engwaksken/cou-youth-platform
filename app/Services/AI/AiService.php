<?php

namespace App\Services\AI;

use App\Models\AiSetting;
use App\Models\AiUsageLog;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class AiService
{
    public function ask(string $message, string $module = 'chatbot', ?int $userId = null): string
    {
        $setting = AiSetting::query()->where('is_enabled', true)->latest('id')->first();
        if (! $setting || ! $setting->api_key || ! $setting->model) {
            throw new RuntimeException('AI is not configured.');
        }

        $started = microtime(true);
        try {
            return match (strtolower($setting->provider)) {
                'openai' => $this->openAi($setting, $message, $module, $userId, $started),
                default => throw new RuntimeException('Unsupported AI provider.'),
            };
        } catch (Throwable $e) {
            AiUsageLog::create([
                'user_id' => $userId,
                'module' => $module,
                'provider' => $setting->provider,
                'model' => $setting->model,
                'successful' => false,
                'error_code' => class_basename($e),
                'duration_ms' => (int) ((microtime(true) - $started) * 1000),
            ]);
            report($e);
            throw $e;
        }
    }

    private function openAi(AiSetting $setting, string $message, string $module, ?int $userId, float $started): string
    {
        $endpoint = rtrim($setting->api_endpoint ?: 'https://api.openai.com/v1', '/').'/responses';

        $response = Http::withToken($setting->api_key)
            ->acceptJson()
            ->timeout($setting->timeout_seconds)
            ->retry($setting->retry_count, 500)
            ->post($endpoint, [
                'model' => $setting->model,
                'input' => [
                    ['role' => 'system', 'content' => trim(($setting->system_prompt ?? '')."\n".($setting->safety_prompt ?? ''))],
                    ['role' => 'user', 'content' => $message],
                ],
                'temperature' => (float) $setting->temperature,
                'max_output_tokens' => $setting->max_tokens,
            ]);

        $response->throw();
        $data = $response->json();
        $text = data_get($data, 'output.0.content.0.text') ?: data_get($data, 'output_text');

        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException('AI returned an empty response.');
        }

        AiUsageLog::create([
            'user_id' => $userId,
            'module' => $module,
            'provider' => $setting->provider,
            'model' => $setting->model,
            'input_tokens' => data_get($data, 'usage.input_tokens'),
            'output_tokens' => data_get($data, 'usage.output_tokens'),
            'successful' => true,
            'duration_ms' => (int) ((microtime(true) - $started) * 1000),
        ]);

        return trim($text);
    }
}
