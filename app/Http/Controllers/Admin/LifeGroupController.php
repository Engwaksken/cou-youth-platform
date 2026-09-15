<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller; use App\Models\LifeGroup; use App\Services\Access\HierarchyScopeService; use Illuminate\Http\Request;
class LifeGroupController extends Controller { public function __construct(private HierarchyScopeService $scope){}
 public function index(){ return view('admin.life_groups.index',['groups'=>LifeGroup::with('organisationUnit')->withCount('members')->orderBy('name')->paginate(25)]); }
 public function store(Request $r){ $data=$this->validated($r); if(!empty($data['organisation_unit_id'])&&!$this->scope->canManage($r->user(),(int)$data['organisation_unit_id']))abort(403); LifeGroup::create($data+['is_active'=>true]); return back()->with('success','Life Group created successfully.'); }
 public function update(Request $r, LifeGroup $lifeGroup){ if($lifeGroup->organisation_unit_id&&!$this->scope->canManage($r->user(),$lifeGroup->organisation_unit_id))abort(403); $lifeGroup->update($this->validated($r)); return back()->with('success','Life Group updated successfully.'); }
 public function destroy(Request $r, LifeGroup $lifeGroup){ if($lifeGroup->organisation_unit_id&&!$this->scope->canManage($r->user(),$lifeGroup->organisation_unit_id))abort(403); $lifeGroup->update(['is_active'=>false]); return back()->with('success','Life Group deactivated.'); }
 private function validated(Request $r): array { return $r->validate(['organisation_unit_id'=>'nullable|exists:organisation_units,id','name'=>'required|max:180','description'=>'nullable','leader_user_id'=>'nullable|exists:users,id','member_limit'=>'required|integer|min:2|max:12','meeting_day'=>'nullable|max:20','meeting_time'=>'nullable','meeting_location'=>'nullable|max:180']); }
}
