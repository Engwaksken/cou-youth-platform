<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Models\{NotificationPreference, PlatformNotification, PlatformNotificationReceipt, User};
use App\Services\Branding\BrandingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class NotificationDeliveryService
{
    public function __construct(private BrandingService $branding) {}

    public function deliver(PlatformNotification $notification, PlatformNotificationReceipt $receipt): void
    {
        $user = User::find($receipt->user_id);
        if (! $user) {
            return;
        }

        $preferences = NotificationPreference::firstOrCreate(['user_id' => $user->id]);
        $channel = $notification->channel ?: 'in_app';

        if (in_array($channel, ['email', 'all'], true) && $preferences->email && $user->email) {
            $brand = $this->branding->data();

            Mail::send('emails.branded-message', [
                'brand' => $brand,
                'subjectLine' => $notification->title,
                'heading' => $notification->title,
                'recipientName' => $user->name,
                'messageBody' => $notification->body,
                'actionUrl' => $notification->action_url,
                'actionLabel' => $notification->action_url ? 'View on COU Youth Platform' : null,
            ], function ($message) use ($user, $notification, $brand): void {
                $message->to($user->email, $user->name)
                    ->subject($notification->title.' | '.($brand['short_name'] ?? 'COU Youth Platform'));
            });
        }

        if (in_array($channel, ['push', 'all'], true) && $preferences->push) {
            $tokens = DB::table('user_devices')
                ->where('user_id', $user->id)
                ->whereNotNull('push_token')
                ->pluck('push_token');

            foreach ($tokens as $token) {
                $this->sendPush((string) $token, $notification);
            }
        }

        $receipt->update(['delivered_at' => now()]);
    }

    private function sendPush(string $token, PlatformNotification $notification): void
    {
        $bridge = config('services.cou_push.bridge_url');
        $secret = config('services.cou_push.secret');
        if (! $bridge) {
            return;
        }

        $brand = $this->branding->data();

        Http::timeout(15)
            ->withToken((string) $secret)
            ->post($bridge, [
                'token' => $token,
                'title' => $notification->title,
                'body' => $notification->body,
                'action_url' => $notification->action_url,
                'brand_name' => $brand['short_name'] ?? 'COU Youth Platform',
                'image_url' => $brand['logo_url'] ?? null,
                'primary_color' => $brand['primary_color'] ?? '#4B2E83',
            ])
            ->throw();
    }
}
