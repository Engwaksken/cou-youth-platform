<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiSetting;
use App\Models\AiUsageLog;
use App\Services\AI\AiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class AiSettingController extends Controller
{
    public function index(Request $request): View
    {
        $setting = AiSetting::query()->latest('id')->first();

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
                    ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('successful', $request->query('status') === 'successful');
        }

        if ($request->filled('module')) {
            $query->where('module', $request->query('module'));
        }

        $recentUsage = $query->paginate(12)->withQueryString();
        $modules = AiUsageLog::query()->whereNotNull('module')->distinct()->orderBy('module')->pluck('module');

        $chart = collect(range(6, 0))->map(function (int $daysAgo): array {
            $date = today()->subDays($daysAgo);
            return [
                'label' => $date->format('D'),
                'total' => AiUsageLog::query()->whereDate('created_at', $date)->count(),
            ];
        });

        return view('admin.ai.index', compact('setting', 'usage', 'recentUsage', 'modules', 'chart'));
    }

    public function update(Request $request): RedirectResponse
    {
        $setting = AiSetting::query()->latest('id')->first();

        $validated = $request->validate([
            'provider' => ['required', 'string', 'max:40'],
            'model' => ['required', 'string', 'max:120'],
            'api_key' => ['nullable', 'string', 'max:2048'],
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

        $data = [
            ...$validated,
            'provider' => strtolower(trim($validated['provider'])),
            'model' => trim($validated['model']),
            'is_enabled' => $request->boolean('is_enabled'),
        ];

        if (blank($validated['api_key'] ?? null)) unset($data['api_key']);

        if ($setting) {
            $setting->update($data);
        } else {
            if (blank($validated['api_key'] ?? null)) return back()->withErrors(['api_key' => 'An API key is required for the first AI configuration.'])->withInput();
            AiSetting::create($data);
        }

        return back()->with('success', 'AI settings updated successfully.');
    }

    public function test(Request $request, AiService $ai): RedirectResponse
    {
        try {
            $response = $ai->ask('Reply with one short sentence confirming that the Church of Uganda Youth Platform AI connection is working.','admin_connection_test',$request->user()?->id);
            return back()->with('success', 'AI connection successful: '.$response);
        } catch (Throwable $exception) {
            report($exception);
            return back()->withErrors(['ai_test' => 'The AI connection test failed. Check the provider, model, endpoint, API key and account availability. Provider details are hidden for security.']);
        }
    }
}
