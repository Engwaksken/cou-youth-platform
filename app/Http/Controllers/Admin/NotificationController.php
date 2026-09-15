<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller; use App\Models\PlatformNotification; use App\Services\Access\HierarchyScopeService; use Illuminate\Http\Request;
class NotificationController extends Controller { public function __construct(private HierarchyScopeService $scope){}
 public function index(){ return view('admin.notifications.index',['items'=>PlatformNotification::latest()->paginate(25)]); }
 public function store(Request $r){ $data=$r->validate(['title'=>'required|max:180','body'=>'required|max:2000','channel'=>'required|in:in_app,push,email,all','organisation_unit_id'=>'nullable|exists:organisation_units,id','age_category'=>'required|in:teen,youth,young_adult,all','action_url'=>'nullable|max:255','scheduled_at'=>'nullable|date']); if(!empty($data['organisation_unit_id'])&&!$this->scope->canManage($r->user(),(int)$data['organisation_unit_id']))abort(403); PlatformNotification::create($data+['created_by'=>$r->user()->id,'is_active'=>true]); return back()->with('success','Notification queued successfully.'); }
}
