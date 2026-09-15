<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller; use App\Models\OrganisationUnit; use App\Services\Access\HierarchyScopeService; use Illuminate\Http\Request;
class OrganisationUnitController extends Controller {
 public function __construct(private HierarchyScopeService $scope){}
 public function index(Request $r){ $q=OrganisationUnit::query(); if(!$this->isSuper($r)) $q->whereIn('id',$this->scope->allowedUnitIds($r->user())); return view('admin.organisation.index',['units'=>$q->with('parent')->orderBy('type')->orderBy('name')->paginate(25)]); }
 public function store(Request $r){ $data=$r->validate(['parent_id'=>'nullable|exists:organisation_units,id','type'=>'required|in:province,diocese,archdeaconry,parish,local_church,chaplaincy,institution','name'=>'required|max:160','code'=>'nullable|max:50','email'=>'nullable|email','phone'=>'nullable|max:40']); if($data['type']!=='province' && !$this->isSuper($r) && !$this->scope->canManage($r->user(),$data['parent_id']??null)) abort(403); OrganisationUnit::create($data+['is_active'=>true]); return back()->with('success','Church structure added successfully.'); }
 public function update(Request $r, OrganisationUnit $organisationUnit){ if(!$this->isSuper($r)&&!$this->scope->canManage($r->user(),$organisationUnit->id)) abort(403); $organisationUnit->update($r->validate(['name'=>'required|max:160','code'=>'nullable|max:50','email'=>'nullable|email','phone'=>'nullable|max:40','is_active'=>'boolean'])); return back()->with('success','Church structure updated successfully.'); }
 public function destroy(Request $r, OrganisationUnit $organisationUnit){ if(!$this->isSuper($r)&&!$this->scope->canManage($r->user(),$organisationUnit->id)) abort(403); abort_if($organisationUnit->children()->exists(),422,'Move or remove child units first.'); $organisationUnit->delete(); return back()->with('success','Church structure removed.'); }
 private function isSuper(Request $r): bool { return method_exists($r->user(),'hasRole') && $r->user()->hasRole('super_admin'); }
}
