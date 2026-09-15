<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller; use App\Models\NotificationPreference; use Illuminate\Http\Request;
class NotificationPreferenceController extends Controller {
 public function show(Request $r){return response()->json(NotificationPreference::firstOrCreate(['user_id'=>$r->user()->id]));}
 public function update(Request $r){$data=$r->validate(['in_app'=>'sometimes|boolean','push'=>'sometimes|boolean','email'=>'sometimes|boolean','events'=>'sometimes|boolean','discipleship'=>'sometimes|boolean','opportunities'=>'sometimes|boolean','donations'=>'sometimes|boolean','life_groups'=>'sometimes|boolean']);$p=NotificationPreference::firstOrCreate(['user_id'=>$r->user()->id]);$p->update($data);return response()->json(['message'=>'Notification preferences updated.','preferences'=>$p->fresh()]);}
}
