<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class PaymentGatewayController extends Controller
{
    public function index(Request $request): View
    {
        $query = PaymentGateway::query();

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('provider', 'like', "%{$search}%")
                    ->orWhere('currency', 'like', "%{$search}%");
            });
        }

        $status = (string) $request->query('status', '');
        if ($status === 'enabled') {
            $query->where('is_enabled', true);
        } elseif ($status === 'disabled') {
            $query->where('is_enabled', false);
        }

        $mode = (string) $request->query('mode', '');
        if ($mode === 'test') {
            $query->where('is_test_mode', true);
        } elseif ($mode === 'live') {
            $query->where('is_test_mode', false);
        }

        $items = $query
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.payment_gateways.index', [
            'items' => $items,
            'stats' => [
                'total' => PaymentGateway::count(),
                'enabled' => PaymentGateway::where('is_enabled', true)->count(),
                'live' => PaymentGateway::where('is_test_mode', false)->count(),
                'test' => PaymentGateway::where('is_test_mode', true)->count(),
            ],
            'filters' => [
                'q' => $search,
                'status' => $status,
                'mode' => $mode,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->normaliseRequestSlug($request);
        $data = $this->validated($request);
        $modelData = Arr::only($data, ['name', 'slug', 'provider', 'currency', 'sort_order']);

        PaymentGateway::create([
            ...$modelData,
            'currency' => strtoupper($data['currency']),
            'credentials' => $this->credentials($request),
            'settings' => $this->settings($request),
            'is_enabled' => $request->boolean('is_enabled'),
            'is_test_mode' => $request->boolean('is_test_mode'),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);

        return back()->with('success', 'Payment gateway added successfully.');
    }

    public function update(Request $request, PaymentGateway $paymentGateway): RedirectResponse
    {
        $this->normaliseRequestSlug($request);
        $data = $this->validated($request, $paymentGateway);
        $modelData = Arr::only($data, ['name', 'slug', 'provider', 'currency', 'sort_order']);

        $existingSettings = is_array($paymentGateway->settings) ? $paymentGateway->settings : [];
        $existingCredentials = is_array($paymentGateway->credentials) ? $paymentGateway->credentials : [];

        // Backward compatibility: older records stored webhook_secret in plain settings JSON.
        // Move it into the encrypted credentials payload the next time an administrator saves.
        if (! filled($existingCredentials['webhook_secret'] ?? null)
            && filled($existingSettings['webhook_secret'] ?? null)) {
            $existingCredentials['webhook_secret'] = (string) $existingSettings['webhook_secret'];
        }
        unset($existingSettings['webhook_secret']);

        $paymentGateway->update([
            ...$modelData,
            'currency' => strtoupper($data['currency']),
            'credentials' => $this->credentials($request, $existingCredentials),
            'settings' => $this->settings($request, $existingSettings),
            'is_enabled' => $request->boolean('is_enabled'),
            'is_test_mode' => $request->boolean('is_test_mode'),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);

        return back()->with('success', 'Payment gateway updated successfully.');
    }

    public function destroy(PaymentGateway $paymentGateway): RedirectResponse
    {
        $paymentGateway->delete();

        return back()->with('success', 'Payment gateway deleted successfully.');
    }

    private function validated(Request $request, ?PaymentGateway $paymentGateway = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => [
                'required', 'string', 'max:120',
                Rule::unique('payment_gateways', 'slug')->ignore($paymentGateway?->getKey()),
            ],
            'provider' => ['required', 'string', 'max:80'],
            'currency' => ['required', 'string', 'size:3'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'callback_url' => ['nullable', 'url', 'max:2048'],
            'webhook_url' => ['nullable', 'url', 'max:2048'],
            'initialize_url' => ['nullable', 'url', 'max:2048'],
            'base_url' => ['nullable', 'url', 'max:2048'],
            'target_environment' => ['nullable', 'string', 'max:80'],
            'country' => ['nullable', 'string', 'size:2'],
            'timeout' => ['nullable', 'integer', 'min:5', 'max:120'],
            'webhook_secret' => ['nullable', 'string', 'max:2000'],
            'api_key' => ['nullable', 'string', 'max:2000'],
            'api_secret' => ['nullable', 'string', 'max:2000'],
            'subscription_key' => ['nullable', 'string', 'max:2000'],
            'api_user' => ['nullable', 'string', 'max:255'],
            'client_id' => ['nullable', 'string', 'max:2000'],
            'client_secret' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function credentials(Request $request, array $existing = []): array
    {
        $fields = [
            'api_key',
            'api_secret',
            'subscription_key',
            'api_user',
            'client_id',
            'client_secret',
            'webhook_secret',
        ];

        $credentials = $existing;

        foreach ($fields as $field) {
            if ($request->filled($field)) {
                $credentials[$field] = trim((string) $request->input($field));
            }
        }

        return array_filter($credentials, static fn ($value): bool => filled($value));
    }

    private function settings(Request $request, array $existing = []): array
    {
        $settings = $existing;

        // Secrets never belong in the unencrypted settings JSON.
        unset($settings['webhook_secret']);

        foreach (['callback_url', 'webhook_url', 'initialize_url', 'base_url', 'target_environment', 'country'] as $field) {
            if ($request->filled($field)) {
                $value = trim((string) $request->input($field));
                $settings[$field] = $field === 'country' ? strtoupper($value) : $value;
            } elseif ($request->has($field)) {
                unset($settings[$field]);
            }
        }

        if ($request->filled('timeout')) {
            $settings['timeout'] = (int) $request->input('timeout');
        } elseif ($request->has('timeout')) {
            unset($settings['timeout']);
        }

        return array_filter($settings, static fn ($value): bool => filled($value));
    }

    private function normaliseRequestSlug(Request $request): void
    {
        $source = $request->filled('slug')
            ? (string) $request->input('slug')
            : (string) $request->input('name');

        $request->merge([
            'slug' => Str::slug($source),
            'currency' => strtoupper(trim((string) $request->input('currency'))),
            'country' => strtoupper(trim((string) $request->input('country', ''))),
        ]);
    }
}
