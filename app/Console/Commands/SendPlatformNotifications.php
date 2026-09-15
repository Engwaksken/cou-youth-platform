<?php
namespace App\Console\Commands;
use App\Models\PlatformNotification; use App\Services\Notifications\TargetedNotificationService; use Illuminate\Console\Command;
class SendPlatformNotifications extends Command { protected $signature='cou:send-notifications'; protected $description='Dispatch due targeted platform notifications'; public function handle(TargetedNotificationService $service): int { $items=PlatformNotification::where('is_active',true)->whereNull('sent_at')->where(function($q){$q->whereNull('scheduled_at')->orWhere('scheduled_at','<=',now());})->get(); $sent=0; foreach($items as $item)$sent+=$service->dispatch($item); $this->info("Notification receipts created: {$sent}"); return self::SUCCESS; } }
