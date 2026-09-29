<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuardianConsent;
use App\Models\YouthProfile;
use App\Services\Access\HierarchyScopeService;
use Illuminate\Http\Request;

final class GuardianConsentController extends Controller
{
    public function __construct(private readonly HierarchyScopeService $scope) {}

    public function index(Request $request)
    {
        $base = $this->scopedQuery($request);
        $query = (clone $base)->with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', (string) $request->status);
        }
        if ($request->filled('q')) {
            $search = trim((string) $request->q);
            $query->where(function ($builder) use ($search): void {
                $builder->where('guardian_name', 'like', "%{$search}%")
                    ->orWhere('guardian_email', 'like', "%{$search}%")
                    ->orWhere('guardian_phone', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($user) => $user
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        $stats = [
            'total' => (clone $base)->count(),
            'pending' => (clone $base)->where('status', 'pending')->count(),
            'approved' => (clone $base)->where('status', 'approved')->count(),
            'rejected' => (clone $base)->where('status', 'rejected')->count(),
        ];
        $chart = (clone $base)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.safeguarding.consents', [
            'consents' => $query->paginate(12)->withQueryString(),
            'stats' => $stats,
            'chart' => $chart,
        ]);
    }

    public function approve(Request $request, GuardianConsent $guardianConsent)
    {
        $this->authoriseConsent($request, $guardianConsent);
        $guardianConsent->update([
            'status' => 'approved',
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Guardian consent approved.');
    }

    public function reject(Request $request, GuardianConsent $guardianConsent)
    {
        $this->authoriseConsent($request, $guardianConsent);
        $guardianConsent->update([
            'status' => 'rejected',
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Guardian consent rejected.');
    }

    private function scopedQuery(Request $request)
    {
        $query = GuardianConsent::query();
        if (! $this->scope->isSuperAdmin($request->user())) {
            $query->whereIn('user_id', YouthProfile::query()
                ->whereIn('organisation_unit_id', $this->scope->allowedUnitIds($request->user()))
                ->select('user_id'));
        }

        return $query;
    }

    private function authoriseConsent(Request $request, GuardianConsent $consent): void
    {
        if ($this->scope->isSuperAdmin($request->user())) {
            return;
        }

        $allowed = YouthProfile::query()
            ->where('user_id', $consent->user_id)
            ->whereIn('organisation_unit_id', $this->scope->allowedUnitIds($request->user()))
            ->exists();

        abort_unless($allowed, 403);
    }
}
