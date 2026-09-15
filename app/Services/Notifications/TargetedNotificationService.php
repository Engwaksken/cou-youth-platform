<?php
namespace App\Services\Notifications;
use App\Models\{PlatformNotification,PlatformNotificationReceipt,YouthProfile}; use App\Services\Access\HierarchyScopeService;
class TargetedNotificationService { public function __construct(private HierarchyScopeService $scope, private NotificationDeliveryService $delivery){}
 public function dispatch(PlatformNotification $notification): int { $q=YouthProfile::query();
  if($notification->organisation_unit_id){ $unitIds=$this->descendantsOf($notification->organisation_unit_id); $q->whereIn('organisation_unit_id',$unitIds); }
  if($notification->age_category!=='all')$q->where('age_category',$notification->age_category);
  $ids=$q->pluck('user_id')->unique(); foreach($ids as $id){ $receipt=PlatformNotificationReceipt::firstOrCreate(['platform_notification_id'=>$notification->id,'user_id'=>$id]); try{$this->delivery->deliver($notification,$receipt);}catch(\Throwable $e){report($e);} }
  $notification->update(['sent_at'=>now()]); return $ids->count();
 }
 private function descendantsOf(int $unitId){ $ids=collect([$unitId]); $frontier=collect([$unitId]); while($frontier->isNotEmpty()){ $children=\App\Models\OrganisationUnit::whereIn('parent_id',$frontier)->pluck('id'); $new=$children->diff($ids); if($new->isEmpty())break; $ids=$ids->merge($new)->unique(); $frontier=$new; } return $ids; }
}
