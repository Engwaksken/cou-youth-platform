<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller; use App\Models\PlatformNotificationReceipt; use Illuminate\Http\Request;
class NotificationController extends Controller { public function index(Request $r){ return response()->json(['data'=>PlatformNotificationReceipt::where('user_id',$r->user()->id)->with('notification')->latest()->paginate(20)]); }
 public function read(Request $r, PlatformNotificationReceipt $receipt){ abort_unless($receipt->user_id===$r->user()->id,403); $receipt->update(['read_at'=>now()]); return response()->json(['message'=>'Notification marked as read.']); } }
