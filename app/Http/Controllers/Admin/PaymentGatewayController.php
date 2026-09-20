<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        PaymentGateway::create([
            ...$data,
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

        $existingSettings = is_array($paymentGateway->settings)
            ? $paymentGateway->settings
            : [];

        $update = [
            ...$data,
            'currency' => strtoupper($data['currency']),
            'settings' => $this->settings($request, $existingSettings),
            'is_enabled' => $request->boolean('is_enabled'),
            'is_test_mode' => $request->boolean('is_test_mode'),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ];

        if ($request->filled('api_key') || $request->filled('api_secret')) {
            $existingCredentials = is_array($paymentGateway->credentials)
                ? $paymentGateway->credentials
                : [];

            $update['credentials'] = array_filter([
                'api_key' => $request->filled('api_key')
                    ? trim((string) $request->input('api_key'))
                    : ($existingCredentials['api_key'] ?? null),
                'api_secret' => $request->filled('api_secret')
                    ? trim((string) $request->input('api_secret'))
                    : ($existingCredentials['api_secret'] ?? null),
            ], static fn ($value): bool => filled($value));
        }

        $paymentGateway->update($update);

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
                'required',
                'string',
                'max:120',
                Rule::unique('payment_gateways', 'slug')->ignore($paymentGateway?->getKey()),
            ],
            'provider' => ['required', 'string', 'max:80'],
            'currency' => ['required', 'string', 'size:3'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'callback_url' => ['nullable', 'url', 'max:2048'],
            'webhook_url' => ['nullable', 'url', 'max:2048'],
            'initialize_url' => ['nullable', 'url', 'max:2048'],
            'timeout' => ['nullable', 'integer', 'min:5', 'max:120'],
            'webhook_secret' => ['nullable', 'string', 'max:255'],
            'api_key' => ['nullable', 'string', 'max:2000'],
            'api_secret' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function credentials(Request $request): array
    {
        return array_filter([
            'api_key' => $request->filled('api_key')
                ? trim((string) $request->input('api_key'))
                : null,
            'api_secret' => $request->filled('api_secret')
                ? trim((string) $request->input('api_secret'))
                : null,
        ], static fn ($value): bool => filled($value));
    }

    private function settings(Request $request, array $existing = []): array
    {
        return array_filter([
            'callback_url' => $request->filled('callback_url')
                ? trim((string) $request->input('callback_url'))
                : null,
            'webhook_url' => $request->filled('webhook_url')
                ? trim((string) $request->input('webhook_url'))
                : null,
            'initialize_url' => $request->filled('initialize_url')
                ? trim((string) $request->input('initialize_url'))
                : null,
            'timeout' => $request->filled('timeout')
                ? (int) $request->input('timeout')
                : null,
            'webhook_secret' => $request->filled('webhook_secret')
                ? trim((string) $request->input('webhook_secret'))
                : ($existing['webhook_secret'] ?? null),
        ], static fn ($value): bool => filled($value));
    }

    private function normaliseRequestSlug(Request $request): void
    {
        $source = $request->filled('slug')
            ? (string) $request->input('slug')
            : (string) $request->input('name');

        $request->merge([
            'slug' => Str::slug($source),
            'currency' => strtoupper(trim((string) $request->input('currency'))),
        ]);
    }
}
