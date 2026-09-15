<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller; use App\Models\{Content,Event,GuardianConsent,LifeGroup,OrganisationUnit,YouthProfile,Donation};
class DashboardController extends Controller { public function index(){ return view('admin.dashboard',['stats'=>[
 'youth'=>YouthProfile::count(),'units'=>OrganisationUnit::where('is_active',true)->count(),'events'=>Event::count(),'life_groups'=>LifeGroup::where('is_active',true)->count(),
 'pending_consents'=>GuardianConsent::where('status','pending')->count(),'published_content'=>Content::where('status','published')->count(),'donations'=>Donation::where('status','successful')->sum('amount')
 ]]); } }
