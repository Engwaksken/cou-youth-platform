<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\DonationCampaign;
use App\Services\Notifications\PlatformUpdateNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DonationController extends Controller
{
    public function __construct(private PlatformUpdateNotificationService $updates) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $status = trim((string) $request->string('status'));
        $campaignId = $request->integer('campaign_id');

        $donations = Donation::query()
            ->with(['campaign', 'gateway'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('reference', 'like', "%{$search}%")
                        ->orWhere('receipt_number', 'like', "%{$search}%")
                        ->orWhere('donor_name', 'like', "%{$search}%")
                        ->orWhere('donor_email', 'like', "%{$search}%")
                        ->orWhere('donor_phone', 'like', "%{$search}%")
                        ->orWhere('external_transaction_id', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($campaignId > 0, fn ($query) => $query->where('campaign_id', $campaignId))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $campaigns = DonationCampaign::query()
            ->withSum([
                'donations as amount_raised' => fn ($query) => $query->whereIn('status', ['successful', 'completed', 'paid']),
            ], 'amount')
            ->latest()
            ->get();

        $summary = [
            'total' => Donation::count(),
            'successful' => Donation::whereIn('status', ['successful', 'completed', 'paid'])->count(),
            'pending' => Donation::where('status', 'pending')->count(),
            'failed' => Donation::where('status', 'failed')->count(),
            'amount_raised' => (float) Donation::whereIn('status', ['successful', 'completed', 'paid'])->sum('amount'),
        ];

        return view('admin.donations.index', compact(
            'donations',
            'campaigns',
            'summary',
            'search',
            'status',
            'campaignId',
        ));
    }

    public function storeCampaign(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string'],
            'target_amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['required', Rule::in(['draft', 'published', 'active', 'closed'])],
            'allow_anonymous' => ['sometimes', 'boolean'],
        ]);

        $campaign = DonationCampaign::create([
            ...$validated,
            'slug' => $this->uniqueSlug($validated['title']),
            'currency' => strtoupper($validated['currency']),
            'allow_anonymous' => $request->boolean('allow_anonymous'),
            'created_by' => $request->user()?->id,
        ]);

        if (in_array($campaign->status, ['published', 'active'], true)) {
            $this->updates->notifyYouth(
                'New donation campaign: '.$campaign->title,
                $campaign->description ?: 'A new youth ministry donation campaign is available.',
                route('public.donate'),
                null,
                'all',
                $request->user()?->id,
            );
        }

        return back()->with('success', 'Donation campaign created successfully.');
    }

    public function updateCampaign(Request $request, DonationCampaign $campaign): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string'],
            'target_amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['required', Rule::in(['draft', 'published', 'active', 'closed'])],
            'allow_anonymous' => ['sometimes', 'boolean'],
        ]);

        $campaign->update([
            ...$validated,
            'slug' => $campaign->title === $validated['title']
                ? $campaign->slug
                : $this->uniqueSlug($validated['title'], $campaign->id),
            'currency' => strtoupper($validated['currency']),
            'allow_anonymous' => $request->boolean('allow_anonymous'),
        ]);

        if (in_array($campaign->status, ['published', 'active'], true)) {
            $this->updates->notifyYouth(
                'Donation campaign updated: '.$campaign->title,
                $campaign->description ?: 'Donation campaign information has been updated.',
                route('public.donate'),
                null,
                'all',
                $request->user()?->id,
            );
        }

        return back()->with('success', 'Donation campaign updated successfully.');
    }

    public function destroyCampaign(Request $request, DonationCampaign $campaign): RedirectResponse
    {
        if ($campaign->donations()->exists()) {
            return back()->withErrors([
                'campaign' => 'This campaign already has donations and cannot be deleted. Close it instead.',
            ]);
        }

        $title = $campaign->title;
        $wasVisible = in_array($campaign->status, ['published', 'active'], true);
        $campaign->delete();

        if ($wasVisible) {
            $this->updates->notifyYouth(
                'Donation campaign removed: '.$title,
                'This donation campaign is no longer available.',
                route('public.donate'),
                null,
                'all',
                $request->user()?->id,
            );
        }

        return back()->with('success', 'Donation campaign deleted successfully.');
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'campaign';
        $slug = $base;
        $counter = 2;

        while (DonationCampaign::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
