<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller; use App\Models\GuardianConsent; use Illuminate\Http\Request;
class GuardianConsentController extends Controller {
 public function index(Request $r){ $q=GuardianConsent::with('user')->latest(); if($r->filled('status')) $q->where('status',$r->status); return view('admin.safeguarding.consents',['consents'=>$q->paginate(25)]); }
 public function approve(Request $r, GuardianConsent $guardianConsent){ $guardianConsent->update(['status'=>'approved','verified_by'=>$r->user()->id,'verified_at'=>now()]); return back()->with('success','Guardian consent approved.'); }
 public function reject(Request $r, GuardianConsent $guardianConsent){ $guardianConsent->update(['status'=>'rejected','verified_by'=>$r->user()->id,'verified_at'=>now()]); return back()->with('success','Guardian consent rejected.'); }
}
