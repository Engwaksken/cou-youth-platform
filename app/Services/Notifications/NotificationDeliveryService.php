<?php
namespace App\Services\Notifications;
use App\Models\{NotificationPreference,PlatformNotification,PlatformNotificationReceipt,User}; use Illuminate\Support\Facades\Http; use Illuminate\Support\Facades\Mail;
class NotificationDeliveryService {
 public function deliver(PlatformNotification $notification, PlatformNotificationReceipt $receipt): void { $user=User::find($receipt->user_id); if(!$user)return; $prefs=NotificationPreference::firstOrCreate(['user_id'=>$user->id]); $channel=$notification->channel ?: 'in_app';
  if(in_array($channel,['email','all'],true)&&$prefs->email&&$user->email){Mail::raw($notification->body,fn($m)=>$m->to($user->email)->subject($notification->title));}
  if(in_array($channel,['push','all'],true)&&$prefs->push){$tokens=\DB::table('user_devices')->where('user_id',$user->id)->whereNotNull('push_token')->pluck('push_token');foreach($tokens as $token)$this->sendPush($token,$notification);}
  $receipt->update(['delivered_at'=>now()]); }
 private function sendPush(string $token,PlatformNotification $n): void { $bridge=config('services.cou_push.bridge_url');$secret=config('services.cou_push.secret');if(!$bridge)return;Http::timeout(15)->withToken((string)$secret)->post($bridge,['token'=>$token,'title'=>$n->title,'body'=>$n->body,'action_url'=>$n->action_url])->throw(); }
}
