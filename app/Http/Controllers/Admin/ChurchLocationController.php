<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChurchLocation;
use App\Models\OrganisationUnit;
use App\Services\Access\HierarchyScopeService;
use App\Services\Notifications\PlatformUpdateNotificationService;
use Illuminate\Http\Request;

final class ChurchLocationController extends Controller
{
    public function __construct(
        private PlatformUpdateNotificationService $updates,
        private HierarchyScopeService $scope,
    ) {}

    public function index(Request $request)
    {
        $base = ChurchLocation::query();
        $this->scope->scopeQuery($base, $request->user());

        $query = (clone $base)->with('organisationUnit')->latest();
        if ($request->filled('q')) {
            $search = trim((string) $request->q);
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('organisationUnit', fn ($unit) => $unit->where('name', 'like', "%{$search}%"));
            });
        }
        if ($request->filled('organisation_unit_id')) {
            $unitId = $request->integer('organisation_unit_id');
            abort_unless($this->scope->canManage($request->user(), $unitId), 403);
            $query->where('organisation_unit_id', $unitId);
        }
        if ($request->filled('coordinates')) {
            $request->coordinates === 'mapped'
                ? $query->whereNotNull('latitude')->whereNotNull('longitude')
                : $query->where(fn ($builder) => $builder->whereNull('latitude')->orWhereNull('longitude'));
        }

        $allowedUnitIds = $this->scope->allowedUnitIds($request->user());
        $units = OrganisationUnit::query()
            ->when(! $this->scope->isSuperAdmin($request->user()), fn ($builder) => $builder->whereIn('id', $allowedUnitIds))
            ->orderBy('name')
            ->get(['id', 'name']);

        $stats = [
            'total' => (clone $base)->count(),
            'mapped' => (clone $base)->whereNotNull('latitude')->whereNotNull('longitude')->count(),
            'with_youth_times' => (clone $base)->whereNotNull('youth_fellowship_times')->where('youth_fellowship_times', '!=', '')->count(),
            'with_contact' => (clone $base)->where(function ($builder): void {
                $builder->whereNotNull('phone')->orWhereNotNull('email');
            })->count(),
        ];

        $chart = (clone $base)
            ->join('organisation_units', 'organisation_units.id', '=', 'church_locations.organisation_unit_id')
            ->selectRaw('organisation_units.type as label, COUNT(*) as total')
            ->groupBy('organisation_units.type')
            ->pluck('total', 'label');

        return view('admin.church_locations.index', [
            'items' => $query->paginate(12)->withQueryString(),
            'units' => $units,
            'stats' => $stats,
            'chart' => $chart,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->authoriseUnit($request, (int) $data['organisation_unit_id']);
        $location = ChurchLocation::create($data);

        $this->updates->notifyYouth(
            'Church location added: '.$location->name,
            'A Church of Uganda location has been added or made available in the Church Locator.',
            route('public.churches'),
            $location->organisation_unit_id,
            'all',
            $request->user()?->id,
        );

        return back()->with('success', 'Church location added.');
    }

    public function update(Request $request, ChurchLocation $churchLocation)
    {
        $this->authoriseUnit($request, $churchLocation->organisation_unit_id);
        $data = $this->validated($request);
        $this->authoriseUnit($request, (int) $data['organisation_unit_id']);
        $churchLocation->update($data);

        $this->updates->notifyYouth(
            'Church location updated: '.$churchLocation->name,
            'Church location, contact or fellowship information has been updated.',
            route('public.churches'),
            $churchLocation->organisation_unit_id,
            'all',
            $request->user()?->id,
        );

        return back()->with('success', 'Church location updated.');
    }

    public function destroy(Request $request, ChurchLocation $churchLocation)
    {
        $this->authoriseUnit($request, $churchLocation->organisation_unit_id);
        $name = $churchLocation->name;
        $unitId = $churchLocation->organisation_unit_id;
        $churchLocation->delete();

        $this->updates->notifyYouth(
            'Church location removed: '.$name,
            'This location is no longer available in the Church Locator.',
            route('public.churches'),
            $unitId,
            'all',
            $request->user()?->id,
        );

        return back()->with('success', 'Church location deleted.');
    }

    private function authoriseUnit(Request $request, int $unitId): void
    {
        if (! $this->scope->canManage($request->user(), $unitId)) {
            abort(403);
        }
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'organisation_unit_id' => 'required|exists:organisation_units,id',
            'name' => 'required|string|max:190',
            'address' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'service_times' => 'nullable|string|max:1000',
            'youth_fellowship_times' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:40',
            'email' => 'nullable|email|max:190',
        ]);
    }
}
