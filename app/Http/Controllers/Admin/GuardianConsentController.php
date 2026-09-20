<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\GuardianConsent;
use Illuminate\Http\Request;

class GuardianConsentController extends Controller
{
    public function index(Request $r)
    {
        $q = GuardianConsent::with('user')->latest();
        if ($r->filled('status')) $q->where('status', $r->status);
        if ($r->filled('q')) {
            $search = trim((string) $r->q);
            $q->where(function ($builder) use ($search) {
                $builder->where('guardian_name','like',"%{$search}%")
                    ->orWhere('guardian_email','like',"%{$search}%")
                    ->orWhere('guardian_phone','like',"%{$search}%")
                    ->orWhereHas('user', fn($user) => $user->where('name','like',"%{$search}%")->orWhere('email','like',"%{$search}%"));
            });
        }

        $stats = [
            'total' => GuardianConsent::count(),
            'pending' => GuardianConsent::where('status','pending')->count(),
            'approved' => GuardianConsent::where('status','approved')->count(),
            'rejected' => GuardianConsent::where('status','rejected')->count(),
        ];
        $chart = GuardianConsent::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total','status');

        return view('admin.safeguarding.consents', [
            'consents' => $q->paginate(12)->withQueryString(),
            'stats' => $stats,
            'chart' => $chart,
        ]);
    }

    public function approve(Request $r, GuardianConsent $guardianConsent)
    {
        $guardianConsent->update(['status'=>'approved','verified_by'=>$r->user()->id,'verified_at'=>now()]);
        return back()->with('success','Guardian consent approved.');
    }

    public function reject(Request $r, GuardianConsent $guardianConsent)
    {
        $guardianConsent->update(['status'=>'rejected','verified_by'=>$r->user()->id,'verified_at'=>now()]);
        return back()->with('success','Guardian consent rejected.');
    }
}
