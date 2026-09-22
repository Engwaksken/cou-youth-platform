<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BulkMessage;
use App\Models\OrganisationUnit;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BulkMessageController extends Controller
{
    public function index()
    {
        return view('admin.bulk.index', [
            'items' => BulkMessage::latest()->paginate(15),
            'departments' => OrganisationUnit::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'type']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'channel' => 'required|in:sms,email',
            'audience_type' => 'required|in:all,department',
            'organisation_unit_id' => 'nullable|required_if:audience_type,department|exists:organisation_units,id',
            'subject' => 'nullable|string|max:180',
            'body' => 'required|string|max:5000',
        ]);

        $users = $this->recipientQuery($data['audience_type'], $data['organisation_unit_id'] ?? null);
        $count = (clone $users)->count();
        $status = 'sent';

        $audience = 'All users';
        if ($data['audience_type'] === 'department') {
            $department = OrganisationUnit::find((int) $data['organisation_unit_id']);
            $audience = $department ? 'Department: '.$department->name : 'Department';
        }

        if ($data['channel'] === 'sms') {
            $username = SiteSetting::get('sms_username');
            $apiKey = SiteSetting::get('sms_api_key');
            $sender = SiteSetting::get('sms_sender', '');

            if ($username && $apiKey) {
                try {
                    $phones = (clone $users)
                        ->whereNotNull('phone')
                        ->pluck('phone')
                        ->filter(fn ($phone) => trim((string) $phone) !== '')
                        ->unique()
                        ->values()
                        ->all();

                    if ($phones) {
                        foreach (array_chunk($phones, 100) as $chunk) {
                            Http::timeout(20)
                                ->withHeaders([
                                    'apiKey' => $apiKey,
                                    'Content-Type' => 'application/x-www-form-urlencoded',
                                    'Accept' => 'application/json',
                                ])
                                ->asForm()
                                ->post('https://api.africastalking.com/version1/messaging', [
                                    'username' => $username,
                                    'to' => implode(',', $chunk),
                                    'message' => $data['body'],
                                    'from' => $sender,
                                ])
                                ->throw();
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('Bulk SMS send failed: '.$e->getMessage());
                    $status = 'partial';
                }
            } else {
                Log::info('Bulk SMS queued: Africa\'s Talking is not configured.');
                $status = 'queued';
            }
        } else {
            try {
                $emails = (clone $users)
                    ->whereNotNull('email')
                    ->pluck('email')
                    ->filter(fn ($email) => trim((string) $email) !== '')
                    ->unique()
                    ->values();

                foreach ($emails as $email) {
                    Mail::raw($data['body'], function ($message) use ($email, $data): void {
                        $message->to($email)
                            ->subject($data['subject'] ?: 'Church of Uganda Youth Platform');
                    });
                }
            } catch (\Throwable $e) {
                Log::warning('Bulk email send failed: '.$e->getMessage());
                $status = 'partial';
            }
        }

        BulkMessage::create([
            'channel' => $data['channel'],
            'subject' => $data['subject'] ?? null,
            'body' => $data['body'],
            'audience' => $audience,
            'recipient_count' => $count,
            'status' => $status,
            'created_by' => $request->user()->id,
        ]);

        return back()->with('success', "Message processed for {$count} recipients (status: {$status}).");
    }

    private function recipientQuery(string $audienceType, ?int $organisationUnitId): Builder
    {
        $query = User::query();

        if ($audienceType === 'department' && $organisationUnitId) {
            $query->where(function (Builder $builder) use ($organisationUnitId): void {
                $builder->whereExists(function ($subQuery) use ($organisationUnitId): void {
                    $subQuery->selectRaw('1')
                        ->from('youth_profiles')
                        ->whereColumn('youth_profiles.user_id', 'users.id')
                        ->where('youth_profiles.organisation_unit_id', $organisationUnitId);
                })->orWhereExists(function ($subQuery) use ($organisationUnitId): void {
                    $subQuery->selectRaw('1')
                        ->from('user_organisation_roles')
                        ->whereColumn('user_organisation_roles.user_id', 'users.id')
                        ->where('user_organisation_roles.organisation_unit_id', $organisationUnitId)
                        ->where('user_organisation_roles.is_active', true);
                });
            });
        }

        return $query;
    }
}
