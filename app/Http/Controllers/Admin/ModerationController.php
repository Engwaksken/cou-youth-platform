<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\ContentReport;
use Illuminate\Http\Request;

class ModerationController extends Controller
{
    public function index(Request $r)
    {
        $q = ContentReport::with(['reporter:id,name','reviewer:id,name'])->latest();
        if ($r->filled('status')) $q->where('status', $r->status);
        if ($r->filled('q')) {
            $search = trim((string) $r->q);
            $q->where(function ($builder) use ($search) {
                $builder->where('reason','like',"%{$search}%")
                    ->orWhere('details','like',"%{$search}%")
                    ->orWhereHas('reporter', fn($user) => $user->where('name','like',"%{$search}%"));
            });
        }

        $stats = [
            'total' => ContentReport::count(),
            'open' => ContentReport::where('status','open')->count(),
            'reviewing' => ContentReport::where('status','reviewing')->count(),
            'resolved' => ContentReport::whereIn('status',['resolved','dismissed'])->count(),
        ];
        $chart = ContentReport::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total','status');

        return view('admin.moderation.index', [
            'reports' => $q->paginate(12)->withQueryString(),
            'stats' => $stats,
            'chart' => $chart,
        ]);
    }

    public function resolve(Request $r, ContentReport $report)
    {
        $d=$r->validate(['status'=>'required|in:reviewing,resolved,dismissed','resolution_notes'=>'nullable|string|max:4000']);
        $report->update($d+['reviewed_by'=>$r->user()->id,'reviewed_at'=>now()]);
        return back()->with('success','Report updated.');
    }
}
