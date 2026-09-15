<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Models\AiSetting;
use App\Models\AiUsageLog;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class AiService
{
    public function ask(string $message, string $module = 'chatbot', ?int $userId = null): string
    {
        $setting = AiSetting::query()
            ->where('is_enabled', true)
            ->latest('id')
            ->first();

        if (! $setting || ! $setting->api_key || ! $setting->model) {
            throw new RuntimeException('AI is not configured.');
        }

        $started = microtime(true);

        try {
            $this->ensureWithinLimits($setting, $userId);

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

    private function ensureWithinLimits(AiSetting $setting, ?int $userId): void
    {
        if ((int) $setting->daily_limit > 0) {
            $todayCount = AiUsageLog::query()
                ->whereDate('created_at', today())
                ->count();

            if ($todayCount >= (int) $setting->daily_limit) {
                throw new RuntimeException('AI daily usage limit reached.');
            }
        }

        if ((int) $setting->monthly_limit > 0) {
            $monthCount = AiUsageLog::query()
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count();

            if ($monthCount >= (int) $setting->monthly_limit) {
                throw new RuntimeException('AI monthly usage limit reached.');
            }
        }

        if ($userId && (int) $setting->per_user_daily_limit > 0) {
            $userTodayCount = AiUsageLog::query()
                ->where('user_id', $userId)
                ->whereDate('created_at', today())
                ->count();

            if ($userTodayCount >= (int) $setting->per_user_daily_limit) {
                throw new RuntimeException('AI user daily usage limit reached.');
            }
        }
    }

    private function openAi(
        AiSetting $setting,
        string $message,
        string $module,
        ?int $userId,
        float $started,
    ): string {
        $endpoint = rtrim($setting->api_endpoint ?: 'https://api.openai.com/v1', '/').'/responses';

        $request = Http::withToken($setting->api_key)
            ->acceptJson()
            ->timeout((int) ($setting->timeout_seconds ?: 30));

        $request = $this->applyRetries($request, (int) ($setting->retry_count ?? 0));

        $response = $request->post($endpoint, [
            'model' => $setting->model,
            'input' => [
                [
                    'role' => 'system',
                    'content' => trim(($setting->system_prompt ?? '')."\n".($setting->safety_prompt ?? '')),
                ],
                [
                    'role' => 'user',
                    'content' => $message,
                ],
            ],
            'temperature' => (float) ($setting->temperature ?? 0.3),
            'max_output_tokens' => (int) ($setting->max_tokens ?: 1200),
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

    private function applyRetries(PendingRequest $request, int $retryCount): PendingRequest
    {
        if ($retryCount <= 0) {
            return $request;
        }

        return $request->retry($retryCount + 1, 500);
    }
}
