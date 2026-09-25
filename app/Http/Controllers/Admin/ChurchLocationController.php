<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChurchLocation;
use App\Models\OrganisationUnit;
use App\Services\Notifications\PlatformUpdateNotificationService;
use Illuminate\Http\Request;

class ChurchLocationController extends Controller
{
    public function __construct(private PlatformUpdateNotificationService $updates) {}

    public function index(Request $r)
    {
        $q = ChurchLocation::with('organisationUnit')->latest();
        if ($r->filled('q')) {
            $search = trim((string) $r->q);
            $q->where(function ($builder) use ($search) {
                $builder->where('name','like',"%{$search}%")
                    ->orWhere('address','like',"%{$search}%")
                    ->orWhere('phone','like',"%{$search}%")
                    ->orWhere('email','like',"%{$search}%")
                    ->orWhereHas('organisationUnit', fn($unit) => $unit->where('name','like',"%{$search}%"));
            });
        }
        if ($r->filled('organisation_unit_id')) $q->where('organisation_unit_id', $r->integer('organisation_unit_id'));
        if ($r->filled('coordinates')) {
            $r->coordinates === 'mapped'
                ? $q->whereNotNull('latitude')->whereNotNull('longitude')
                : $q->where(fn($x) => $x->whereNull('latitude')->orWhereNull('longitude'));
        }

        $stats = [
            'total' => ChurchLocation::count(),
            'mapped' => ChurchLocation::whereNotNull('latitude')->whereNotNull('longitude')->count(),
            'with_youth_times' => ChurchLocation::whereNotNull('youth_fellowship_times')->where('youth_fellowship_times','!=','')->count(),
            'with_contact' => ChurchLocation::where(function($x){$x->whereNotNull('phone')->orWhereNotNull('email');})->count(),
        ];
        $chart = ChurchLocation::query()->join('organisation_units','organisation_units.id','=','church_locations.organisation_unit_id')
            ->selectRaw('organisation_units.type as label, COUNT(*) as total')->groupBy('organisation_units.type')->pluck('total','label');

        return view('admin.church_locations.index', [
            'items' => $q->paginate(12)->withQueryString(),
            'units' => OrganisationUnit::orderBy('name')->get(['id','name']),
            'stats' => $stats,
            'chart' => $chart,
        ]);
    }

    public function store(Request $r)
    {
        $location = ChurchLocation::create($this->validated($r));

        $this->updates->notifyYouth(
            'Church location added: '.$location->name,
            'A Church of Uganda location has been added or made available in the Church Locator.',
            route('public.churches'),
            $location->organisation_unit_id,
            'all',
            $r->user()?->id,
        );

        return back()->with('success','Church location added.');
    }

    public function update(Request $r, ChurchLocation $churchLocation)
    {
        $churchLocation->update($this->validated($r));

        $this->updates->notifyYouth(
            'Church location updated: '.$churchLocation->name,
            'Church location, contact or fellowship information has been updated.',
            route('public.churches'),
            $churchLocation->organisation_unit_id,
            'all',
            $r->user()?->id,
        );

        return back()->with('success','Church location updated.');
    }

    public function destroy(Request $r, ChurchLocation $churchLocation)
    {
        $name = $churchLocation->name;
        $unitId = $churchLocation->organisation_unit_id;
        $churchLocation->delete();

        $this->updates->notifyYouth(
            'Church location removed: '.$name,
            'This location is no longer available in the Church Locator.',
            route('public.churches'),
            $unitId,
            'all',
            $r->user()?->id,
        );

        return back()->with('success','Church location deleted.');
    }

    private function validated(Request $r): array
    {
        return $r->validate([
            'organisation_unit_id'=>'required|exists:organisation_units,id',
            'name'=>'required|string|max:190',
            'address'=>'nullable|string|max:500',
            'latitude'=>'nullable|numeric|between:-90,90',
            'longitude'=>'nullable|numeric|between:-180,180',
            'service_times'=>'nullable|string|max:1000',
            'youth_fellowship_times'=>'nullable|string|max:1000',
            'phone'=>'nullable|string|max:40',
            'email'=>'nullable|email|max:190',
        ]);
    }
}
