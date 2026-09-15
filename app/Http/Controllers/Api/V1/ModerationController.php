<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller; use App\Models\{ContentReport,User,UserBlock}; use Illuminate\Http\Request;
class ModerationController extends Controller {
 public function report(Request $r){$d=$r->validate(['reportable_type'=>'required|in:content,prayer_request,life_group,media_asset,user','reportable_id'=>'required|integer|min:1','reason'=>'required|string|max:120','details'=>'nullable|string|max:3000']);$report=ContentReport::create($d+['reporter_id'=>$r->user()->id]);return response()->json(['message'=>'Report submitted for review.','report'=>$report],201);}
 public function block(Request $r,User $user){abort_if($user->id===$r->user()->id,422,'You cannot block yourself.');$d=$r->validate(['reason'=>'nullable|string|max:255']);$block=UserBlock::firstOrCreate(['blocker_id'=>$r->user()->id,'blocked_id'=>$user->id],['reason'=>$d['reason']??null]);return response()->json(['message'=>'User blocked.','block'=>$block],201);}
 public function unblock(Request $r,User $user){UserBlock::where('blocker_id',$r->user()->id)->where('blocked_id',$user->id)->delete();return response()->json(['message'=>'User unblocked.']);}
 public function blocked(Request $r){return response()->json(UserBlock::where('blocker_id',$r->user()->id)->with('blocked:id,name')->latest()->paginate(30));}
}
