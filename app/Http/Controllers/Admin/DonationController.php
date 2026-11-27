<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\DonationCampaign;
use App\Models\OrganisationUnit;
use App\Support\ChurchOfUgandaDioceses;
use Illuminate\Database\Eloquent\Builder;
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
        $dioceseId = $request->integer('diocese_id');
        $successful = ['successful', 'completed', 'paid'];

        // Every total on the page honours the diocese filter.
        $base = fn (): Builder => Donation::query()
            ->when($dioceseId > 0, fn ($query) => $query->where('diocese_id', $dioceseId));

        $donations = $base()
            ->with(['campaign', 'gateway', 'diocese'])
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
                'donations as amount_raised' => fn ($query) => $query
                    ->whereIn('status', $successful)
                    ->when($dioceseId > 0, fn ($q) => $q->where('diocese_id', $dioceseId)),
            ], 'amount')
            ->latest()
            ->get();

        $summary = [
            'total' => $base()->count(),
            'successful' => $base()->whereIn('status', $successful)->count(),
            'pending' => $base()->where('status', 'pending')->count(),
            'failed' => $base()->where('status', 'failed')->count(),
            'amount_raised' => (float) $base()->whereIn('status', $successful)->sum('amount'),
        ];

        $dioceses = ChurchOfUgandaDioceses::options();
        $dioceseNames = OrganisationUnit::query()->where('type', 'diocese')->pluck('name', 'id');
        $byDiocese = Donation::query()
            ->whereIn('status', $successful)
            ->selectRaw('diocese_id, COUNT(*) as donation_count, SUM(amount) as amount_raised')
            ->groupBy('diocese_id')
            ->orderByDesc('amount_raised')
            ->get()
            ->map(fn ($row) => (object) [
                'diocese_id' => $row->diocese_id,
                'name' => $row->diocese_id ? ($dioceseNames[$row->diocese_id] ?? 'Removed diocese') : 'No diocese recorded',
                'donation_count' => (int) $row->donation_count,
                'amount_raised' => (float) $row->amount_raised,
            ]);

        return view('admin.donations.index', compact(
            'donations',
            'campaigns',
            'summary',
            'search',
            'status',
            'campaignId',
            'dioceseId',
            'dioceses',
            'byDiocese',
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
