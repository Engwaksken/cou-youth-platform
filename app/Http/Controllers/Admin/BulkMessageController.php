<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BulkMessage;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BulkMessageController extends Controller
{
    public function index()
    {
        return view('admin.bulk.index', ['items' => BulkMessage::latest()->paginate(15)]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'channel' => 'required|in:sms,email',
            'audience' => 'nullable|string|max:80',
            'subject' => 'nullable|string|max:180',
            'body' => 'required|string|max:5000',
        ]);
        $audience = $data['audience'] ?? 'all';
        $count = User::count();
        $status = 'sent';
        if ($data['channel'] === 'sms') {
            $username = SiteSetting::get('sms_username');
            $apiKey = SiteSetting::get('sms_api_key');
            $sender = SiteSetting::get('sms_sender', '');
            if ($username && $apiKey) {
                try {
                    $phones = User::whereNotNull('phone')->pluck('phone')->filter()->take(100)->values()->all();
                    if ($phones) {
                        Http::timeout(15)->withHeaders(['apiKey' => $apiKey, 'Content-Type' => 'application/x-www-form-urlencoded', 'Accept' => 'application/json'])
                            ->asForm()->post('https://api.africastalking.com/version1/messaging', ['username' => $username, 'to' => implode(',', $phones), 'message' => $data['body'], 'from' => $sender]);
                    }
                } catch (\Throwable $e) {
                    Log::warning('Bulk SMS send failed: ' . $e->getMessage());
                    $status = 'partial';
                }
            } else {
                Log::info('Bulk SMS skipped: Africa\'s Talking not configured.');
                $status = 'queued';
            }
        } else {
            try {
                $emails = User::whereNotNull('email')->pluck('email')->filter()->take(50)->values()->all();
                foreach ($emails as $email) {
                    Mail::raw($data['body'], function ($m) use ($email, $data) {
                        $m->to($email)->subject($data['subject'] ?? 'Message');
                    });
                }
            } catch (\Throwable $e) {
                Log::warning('Bulk email send failed: ' . $e->getMessage());
                $status = 'partial';
            }
        }
        BulkMessage::create(['channel' => $data['channel'], 'subject' => $data['subject'] ?? null, 'body' => $data['body'], 'audience' => $audience, 'recipient_count' => $count, 'status' => $status, 'created_by' => $request->user()->id]);
        return back()->with('success', "Message processed for {$count} recipients (status: {$status}).");
    }
}
