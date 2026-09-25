<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiSetting;
use App\Models\AiUsageLog;
use App\Services\AI\AiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class AiSettingController extends Controller
{
    public function index(Request $request): View
    {
        $settings = AiSetting::query()->latest('id')->get();
        $activeSetting = $settings->firstWhere('is_enabled', true);

        $usage = [
            'today_requests' => AiUsageLog::query()->whereDate('created_at', today())->count(),
            'month_requests' => AiUsageLog::query()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            'successful' => AiUsageLog::query()->where('successful', true)->count(),
            'failed' => AiUsageLog::query()->where('successful', false)->count(),
            'input_tokens' => (int) AiUsageLog::query()->sum('input_tokens'),
            'output_tokens' => (int) AiUsageLog::query()->sum('output_tokens'),
        ];

        $query = AiUsageLog::query()->with('user:id,name,email')->latest();

        if ($request->filled('q')) {
            $search = trim((string) $request->query('q'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('module', 'like', "%{$search}%")
                    ->orWhere('provider', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($user) => $user
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('successful', $request->query('status') === 'successful');
        }

        if ($request->filled('module')) {
            $query->where('module', $request->query('module'));
        }

        $recentUsage = $query->paginate(20)->withQueryString();
        $modules = AiUsageLog::query()
            ->whereNotNull('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module');

        $chart = collect(range(6, 0))->map(function (int $daysAgo): array {
            $date = today()->subDays($daysAgo);

            return [
                'label' => $date->format('D'),
                'total' => AiUsageLog::query()->whereDate('created_at', $date)->count(),
            ];
        });

        return view('admin.ai.index', compact(
            'settings',
            'activeSetting',
            'usage',
            'recentUsage',
            'modules',
            'chart',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateSetting($request, true);
        $data = $this->normaliseSettingData($request, $validated);

        DB::transaction(function () use ($data): void {
            if ($data['is_enabled']) {
                AiSetting::query()->update(['is_enabled' => false]);
            }

            AiSetting::query()->create($data);
        });

        return back()->with('success', 'AI setting created successfully.');
    }

    public function update(Request $request, AiSetting $aiSetting): RedirectResponse
    {
        $validated = $this->validateSetting($request, false);
        $data = $this->normaliseSettingData($request, $validated);

        if (blank($validated['api_key'] ?? null)) {
            unset($data['api_key']);
        }

        DB::transaction(function () use ($aiSetting, $data): void {
            if ($data['is_enabled']) {
                AiSetting::query()
                    ->where('id', '!=', $aiSetting->getKey())
                    ->update(['is_enabled' => false]);
            }

            $aiSetting->update($data);
        });

        return back()->with('success', 'AI setting updated successfully.');
    }

    public function activate(AiSetting $aiSetting): RedirectResponse
    {
        DB::transaction(function () use ($aiSetting): void {
            AiSetting::query()->update(['is_enabled' => false]);
            $aiSetting->update(['is_enabled' => true]);
        });

        return back()->with('success', "{$aiSetting->provider} / {$aiSetting->model} is now the active AI setting.");
    }

    public function destroy(AiSetting $aiSetting): RedirectResponse
    {
        $wasActive = (bool) $aiSetting->is_enabled;
        $aiSetting->delete();

        return back()->with(
            'success',
            $wasActive
                ? 'The active AI setting was deleted. AI will remain unavailable until another setting is activated.'
                : 'AI setting deleted successfully.'
        );
    }

    public function bulkDestroySettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', Rule::exists('ai_settings', 'id')],
        ]);

        $count = AiSetting::query()->whereKey($validated['ids'])->delete();

        return back()->with('success', "{$count} AI setting(s) deleted successfully.");
    }

    public function bulkDestroyUsage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', Rule::exists('ai_usage_logs', 'id')],
        ]);

        $count = AiUsageLog::query()->whereKey($validated['ids'])->delete();

        return back()->with('success', "{$count} AI usage log(s) deleted successfully.");
    }

    public function test(Request $request, AiService $ai): RedirectResponse
    {
        try {
            $response = $ai->ask(
                'Reply with one short sentence confirming that the Church of Uganda Youth Platform AI connection is working.',
                'admin_connection_test',
                $request->user()?->id,
            );

            return back()->with('success', 'AI connection successful: '.$response);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'ai_test' => 'The active AI connection test failed. Check the active provider, model, endpoint, API key and account availability.',
            ]);
        }
    }

    /** @return array<string, mixed> */
    private function validateSetting(Request $request, bool $creating): array
    {
        return $request->validate([
            'provider' => ['required', 'string', 'max:40'],
            'model' => ['required', 'string', 'max:120'],
            'api_key' => [$creating ? 'required' : 'nullable', 'string', 'max:2048'],
            'api_endpoint' => ['nullable', 'url', 'max:500'],
            'temperature' => ['required', 'numeric', 'min:0', 'max:2'],
            'max_tokens' => ['required', 'integer', 'min:64', 'max:100000'],
            'daily_limit' => ['nullable', 'integer', 'min:0'],
            'monthly_limit' => ['nullable', 'integer', 'min:0'],
            'per_user_daily_limit' => ['nullable', 'integer', 'min:0'],
            'timeout_seconds' => ['required', 'integer', 'min:5', 'max:180'],
            'retry_count' => ['required', 'integer', 'min:0', 'max:5'],
            'system_prompt' => ['nullable', 'string', 'max:10000'],
            'safety_prompt' => ['nullable', 'string', 'max:10000'],
            'is_enabled' => ['sometimes', 'boolean'],
        ]);
    }

    /**
     * @param array<string, mixed> $validated
     * @return array<string, mixed>
     */
    private function normaliseSettingData(Request $request, array $validated): array
    {
        return [
            ...$validated,
            'provider' => strtolower(trim((string) $validated['provider'])),
            'model' => trim((string) $validated['model']),
            'api_endpoint' => filled($validated['api_endpoint'] ?? null)
                ? rtrim(trim((string) $validated['api_endpoint']), '/')
                : null,
            'daily_limit' => (int) ($validated['daily_limit'] ?? 0),
            'monthly_limit' => (int) ($validated['monthly_limit'] ?? 0),
            'per_user_daily_limit' => (int) ($validated['per_user_daily_limit'] ?? 0),
            'is_enabled' => $request->boolean('is_enabled'),
        ];
    }
}
