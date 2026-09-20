<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\PlatformNotification;
use App\Services\Access\HierarchyScopeService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private HierarchyScopeService $scope){}

    public function index(Request $r)
    {
        $q = PlatformNotification::query()->latest();

        if ($r->filled('q')) {
            $search = trim((string) $r->q);
            $q->where(function ($builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }
        if ($r->filled('channel')) $q->where('channel', $r->channel);
        if ($r->filled('age_category')) $q->where('age_category', $r->age_category);

        $stats = [
            'total' => PlatformNotification::count(),
            'active' => PlatformNotification::where('is_active', true)->count(),
            'scheduled' => PlatformNotification::whereNotNull('scheduled_at')->where('scheduled_at', '>', now())->count(),
            'all_channel' => PlatformNotification::where('channel', 'all')->count(),
        ];

        $chart = PlatformNotification::query()
            ->selectRaw('channel, COUNT(*) as total')
            ->groupBy('channel')
            ->pluck('total', 'channel');

        return view('admin.notifications.index', [
            'items' => $q->paginate(12)->withQueryString(),
            'stats' => $stats,
            'chart' => $chart,
        ]);
    }

    public function store(Request $r)
    {
        $data=$r->validate([
            'title'=>'required|max:180','body'=>'required|max:2000','channel'=>'required|in:in_app,push,email,all',
            'organisation_unit_id'=>'nullable|exists:organisation_units,id','age_category'=>'required|in:teen,youth,young_adult,all',
            'action_url'=>'nullable|max:255','scheduled_at'=>'nullable|date'
        ]);
        if(!empty($data['organisation_unit_id'])&&!$this->scope->canManage($r->user(),(int)$data['organisation_unit_id'])) abort(403);
        PlatformNotification::create($data+['created_by'=>$r->user()->id,'is_active'=>true]);
        return back()->with('success','Notification queued successfully.');
    }
}
