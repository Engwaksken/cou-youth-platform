<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller; use App\Models\ContentReport; use Illuminate\Http\Request;
class ModerationController extends Controller {
 public function index(Request $r){$q=ContentReport::with(['reporter:id,name','reviewer:id,name'])->latest();if($r->filled('status'))$q->where('status',$r->status);return view('admin.moderation.index',['reports'=>$q->paginate(25)]);}
 public function resolve(Request $r,ContentReport $report){$d=$r->validate(['status'=>'required|in:reviewing,resolved,dismissed','resolution_notes'=>'nullable|string|max:4000']);$report->update($d+['reviewed_by'=>$r->user()->id,'reviewed_at'=>now()]);return back()->with('success','Report updated.');}
}
