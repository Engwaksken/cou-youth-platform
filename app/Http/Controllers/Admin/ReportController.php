<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller; use App\Models\{Event,EventRegistration,LifeGroup,YouthProfile,Donation,Content,OrganisationUnit}; use Illuminate\Http\Request;
class ReportController extends Controller { public function index(Request $r){
 $unit=$r->integer('organisation_unit_id') ?: null; $y=YouthProfile::query(); $e=Event::query(); $lg=LifeGroup::query(); $c=Content::query();
 if($unit){$y->where('organisation_unit_id',$unit);$e->where('organisation_unit_id',$unit);$lg->where('organisation_unit_id',$unit);$c->where('organisation_unit_id',$unit);} 
 return view('admin.reports.index',['units'=>OrganisationUnit::orderBy('name')->get(['id','name']),'summary'=>['youth'=>$y->count(),'teens'=>(clone $y)->where('age_category','teen')->count(),'events'=>$e->count(),'event_registrations'=>EventRegistration::count(),'life_groups'=>$lg->where('is_active',true)->count(),'published_content'=>$c->where('status','published')->count(),'successful_donations'=>Donation::where('status','successful')->sum('amount')]]);
 } }
