<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Models\AiSetting;
use App\Models\AiUsageLog;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class AiService
{
    public function ask(string $message, string $module = 'chatbot', ?int $userId = null): string
    {
        return $this->run($message, $module, $userId, true);
    }

    public function testConnection(?int $userId = null): string
    {
        return $this->run(
            'Reply with exactly: AI connection working',
            'admin_connection_test',
            $userId,
            false,
        );
    }

    private function run(string $message, string $module, ?int $userId, bool $enforceLimits): string
    {
        $setting = $this->activeSetting();
        $started = microtime(true);

        try {
            if ($enforceLimits) {
                $this->ensureWithinLimits($setting, $userId);
            }

            $result = match ($this->normaliseProvider((string) $setting->provider)) {
                'openai' => $this->openAi($setting, $message),
                'deepseek' => $this->openAiCompatible($setting, $message, 'https://api.deepseek.com'),
                'openai_compatible' => $this->openAiCompatible($setting, $message, null),
                'gemini' => $this->gemini($setting, $message),
                default => throw new RuntimeException(
                    'Unsupported AI provider "'.$setting->provider.'". Use OpenAI, DeepSeek, Gemini, or OpenAI Compatible.'
                ),
            };

            $this->logUsage(
                $setting,
                $module,
                $userId,
                true,
                $started,
                $result['input_tokens'],
                $result['output_tokens'],
            );

            return $result['text'];
        } catch (Throwable $e) {
            $exception = $this->normaliseException($e);

            $this->logUsage(
                $setting,
                $module,
                $userId,
                false,
                $started,
                null,
                null,
                class_basename($exception),
            );

            report($e);

            throw $exception;
        }
    }

    private function activeSetting(): AiSetting
    {
        $setting = AiSetting::query()
            ->where('is_enabled', true)
            ->latest('id')
            ->first();

        if (! $setting) {
            throw new RuntimeException('No active AI provider is configured. Activate one AI setting and try again.');
        }

        if (blank($setting->api_key)) {
            throw new RuntimeException('The active AI provider does not have an API key. Save the API key again.');
        }

        if (blank($setting->model)) {
            throw new RuntimeException('The active AI provider does not have a model configured.');
        }

        return $setting;
    }

    private function ensureWithinLimits(AiSetting $setting, ?int $userId): void
    {
        if ((int) $setting->daily_limit > 0) {
            $todayCount = AiUsageLog::query()->whereDate('created_at', today())->count();

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

    /** @return array{text:string,input_tokens:?int,output_tokens:?int} */
    private function openAi(AiSetting $setting, string $message): array
    {
        $endpoint = $this->endpointFor(
            (string) ($setting->api_endpoint ?: 'https://api.openai.com/v1'),
            'responses',
        );

        $payload = [
            'model' => $setting->model,
            'input' => $this->responsesInput($setting, $message),
            'max_output_tokens' => (int) ($setting->max_tokens ?: 1200),
        ];

        if ($this->supportsTemperature((string) $setting->model)) {
            $payload['temperature'] = (float) ($setting->temperature ?? 0.3);
        }

        $response = $this->request($setting)
            ->withToken((string) $setting->api_key)
            ->post($endpoint, $payload);

        $this->throwForProviderError($response);
        $data = $response->json();

        $text = data_get($data, 'output_text');

        if (! is_string($text) || trim($text) === '') {
            $text = data_get($data, 'output.0.content.0.text');
        }

        return [
            'text' => $this->requireText($text),
            'input_tokens' => $this->nullableInt(data_get($data, 'usage.input_tokens')),
            'output_tokens' => $this->nullableInt(data_get($data, 'usage.output_tokens')),
        ];
    }

    /** @return array{text:string,input_tokens:?int,output_tokens:?int} */
    private function openAiCompatible(AiSetting $setting, string $message, ?string $defaultBaseUrl): array
    {
        $baseUrl = trim((string) ($setting->api_endpoint ?: $defaultBaseUrl));

        if ($baseUrl === '') {
            throw new RuntimeException('An API endpoint is required for an OpenAI Compatible provider.');
        }

        $endpoint = $this->endpointFor($baseUrl, 'chat/completions');

        $messages = [];
        $systemPrompt = $this->combinedSystemPrompt($setting);

        if ($systemPrompt !== '') {
            $messages[] = ['role' => 'system', 'content' => $systemPrompt];
        }

        $messages[] = ['role' => 'user', 'content' => $message];

        $response = $this->request($setting)
            ->withToken((string) $setting->api_key)
            ->post($endpoint, [
                'model' => $setting->model,
                'messages' => $messages,
                'temperature' => (float) ($setting->temperature ?? 0.3),
                'max_tokens' => (int) ($setting->max_tokens ?: 1200),
            ]);

        $this->throwForProviderError($response);
        $data = $response->json();

        return [
            'text' => $this->requireText(data_get($data, 'choices.0.message.content')),
            'input_tokens' => $this->nullableInt(data_get($data, 'usage.prompt_tokens')),
            'output_tokens' => $this->nullableInt(data_get($data, 'usage.completion_tokens')),
        ];
    }

    /** @return array{text:string,input_tokens:?int,output_tokens:?int} */
    private function gemini(AiSetting $setting, string $message): array
    {
        $baseUrl = rtrim(
            trim((string) ($setting->api_endpoint ?: 'https://generativelanguage.googleapis.com/v1beta')),
            '/',
        );

        if (str_contains($baseUrl, ':generateContent')) {
            $endpoint = $baseUrl;
        } else {
            $endpoint = $baseUrl.'/models/'.rawurlencode((string) $setting->model).':generateContent';
        }

        $payload = [
            'contents' => [[
                'role' => 'user',
                'parts' => [['text' => $message]],
            ]],
            'generationConfig' => [
                'temperature' => (float) ($setting->temperature ?? 0.3),
                'maxOutputTokens' => (int) ($setting->max_tokens ?: 1200),
            ],
        ];

        $systemPrompt = $this->combinedSystemPrompt($setting);
        if ($systemPrompt !== '') {
            $payload['systemInstruction'] = [
                'parts' => [['text' => $systemPrompt]],
            ];
        }

        $response = $this->request($setting)
            ->withHeaders(['x-goog-api-key' => (string) $setting->api_key])
            ->post($endpoint, $payload);

        $this->throwForProviderError($response);
        $data = $response->json();

        return [
            'text' => $this->requireText(data_get($data, 'candidates.0.content.parts.0.text')),
            'input_tokens' => $this->nullableInt(data_get($data, 'usageMetadata.promptTokenCount')),
            'output_tokens' => $this->nullableInt(data_get($data, 'usageMetadata.candidatesTokenCount')),
        ];
    }

    private function request(AiSetting $setting): PendingRequest
    {
        $request = Http::acceptJson()
            ->asJson()
            ->connectTimeout(min(10, (int) ($setting->timeout_seconds ?: 30)))
            ->timeout((int) ($setting->timeout_seconds ?: 30));

        return $this->applyRetries($request, (int) ($setting->retry_count ?? 0));
    }

    private function applyRetries(PendingRequest $request, int $retryCount): PendingRequest
    {
        if ($retryCount <= 0) {
            return $request;
        }

        return $request->retry($retryCount + 1, 500);
    }

    /** @return array<int, array<string, mixed>> */
    private function responsesInput(AiSetting $setting, string $message): array
    {
        $input = [];
        $systemPrompt = $this->combinedSystemPrompt($setting);

        if ($systemPrompt !== '') {
            $input[] = [
                'role' => 'system',
                'content' => [['type' => 'input_text', 'text' => $systemPrompt]],
            ];
        }

        $input[] = [
            'role' => 'user',
            'content' => [['type' => 'input_text', 'text' => $message]],
        ];

        return $input;
    }

    private function combinedSystemPrompt(AiSetting $setting): string
    {
        return trim(implode("\n\n", array_filter([
            trim((string) ($setting->system_prompt ?? '')),
            trim((string) ($setting->safety_prompt ?? '')),
        ])));
    }

    private function endpointFor(string $baseUrl, string $path): string
    {
        $baseUrl = rtrim(trim($baseUrl), '/');
        $path = trim($path, '/');

        if ($baseUrl === '') {
            throw new RuntimeException('The AI provider endpoint is empty.');
        }

        if (str_ends_with(strtolower($baseUrl), '/'.$path)) {
            return $baseUrl;
        }

        return $baseUrl.'/'.$path;
    }

    private function supportsTemperature(string $model): bool
    {
        $model = strtolower(trim($model));

        return ! preg_match('/^(gpt-5|o[1-9](?:-|$))/', $model);
    }

    private function throwForProviderError(Response $response): void
    {
        if ($response->successful()) {
            return;
        }

        $providerMessage = $this->providerMessage($response);

        $message = match ($response->status()) {
            400 => 'The AI provider rejected the request. Check the selected model and endpoint.',
            401 => 'The AI provider rejected the API key. Save a valid API key and try again.',
            403 => 'The API key is valid but does not have permission to use the selected AI model or service.',
            404 => 'The AI endpoint or model was not found. Check the provider endpoint and exact model name.',
            408 => 'The AI provider timed out while processing the request.',
            422 => 'The AI provider rejected one or more request parameters.',
            429 => 'The AI provider reported a rate-limit, quota, or billing problem. Check the provider account availability.',
            500, 502, 503, 504 => 'The AI provider is temporarily unavailable. Please try again shortly.',
            default => 'The AI provider returned HTTP '.$response->status().'.',
        };

        if ($providerMessage !== null) {
            $message .= ' Provider message: '.$providerMessage;
        }

        throw new RuntimeException($message, $response->status());
    }

    private function providerMessage(Response $response): ?string
    {
        $data = $response->json();
        $message = data_get($data, 'error.message')
            ?? data_get($data, 'message')
            ?? data_get($data, 'error.status');

        if (! is_string($message)) {
            return null;
        }

        $message = trim(preg_replace('/\s+/', ' ', $message) ?? '');

        return $message === '' ? null : mb_substr($message, 0, 400);
    }

    private function normaliseException(Throwable $e): RuntimeException
    {
        if ($e instanceof RuntimeException) {
            return $e;
        }

        if ($e instanceof RequestException && $e->response) {
            try {
                $this->throwForProviderError($e->response);
            } catch (RuntimeException $runtimeException) {
                return $runtimeException;
            }
        }

        $message = strtolower($e->getMessage());

        if (str_contains($message, 'timed out') || str_contains($message, 'timeout')) {
            return new RuntimeException('The AI provider connection timed out. Check the endpoint and internet connection.');
        }

        if (str_contains($message, 'could not resolve') || str_contains($message, 'connection refused')) {
            return new RuntimeException('The AI provider could not be reached. Check the endpoint, DNS, firewall, and internet connection.');
        }

        return new RuntimeException('The AI request could not be completed. Check the provider configuration and application log.');
    }

    private function normaliseProvider(string $provider): string
    {
        $provider = strtolower(trim($provider));
        $provider = str_replace(['-', ' '], '_', $provider);

        return match ($provider) {
            'open_ai' => 'openai',
            'deep_seek' => 'deepseek',
            'google', 'google_gemini' => 'gemini',
            'custom', 'compatible', 'openai-compatible', 'openai_compatible_api' => 'openai_compatible',
            default => $provider,
        };
    }

    private function requireText(mixed $text): string
    {
        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException('The AI provider returned a successful response but no text content was found.');
        }

        return trim($text);
    }

    private function nullableInt(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    private function logUsage(
        AiSetting $setting,
        string $module,
        ?int $userId,
        bool $successful,
        float $started,
        ?int $inputTokens = null,
        ?int $outputTokens = null,
        ?string $errorCode = null,
    ): void {
        AiUsageLog::create([
            'user_id' => $userId,
            'module' => $module,
            'provider' => $setting->provider,
            'model' => $setting->model,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'successful' => $successful,
            'error_code' => $errorCode,
            'duration_ms' => (int) ((microtime(true) - $started) * 1000),
        ]);
    }
}
