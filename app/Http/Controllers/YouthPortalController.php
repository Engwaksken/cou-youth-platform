<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseEnrolment;
use App\Models\LifeGroup;
use App\Models\LifeGroupMember;
use App\Models\MediaAsset;
use App\Models\PlatformNotificationReceipt;
use App\Models\YouthProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class YouthPortalController extends Controller
{
    public function dashboard(Request $request): View
    {
        $user = $request->user();
        $profile = YouthProfile::with('organisationUnit')->where('user_id', $user->id)->first();
        $search = trim((string) $request->query('q', ''));
        $period = (string) $request->query('period', 'month');
        $allowedPeriods = ['today', 'week', 'month', 'year', 'all'];
        if (! in_array($period, $allowedPeriods, true)) {
            $period = 'month';
        }

        $periodBounds = match ($period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            default => [null, null],
        };

        $applyPeriod = static function ($query, string $column = 'created_at') use ($periodBounds) {
            [$from, $to] = $periodBounds;
            if ($from && $to) {
                $query->whereBetween($column, [$from, $to]);
            }

            return $query;
        };

        $membershipQuery = LifeGroupMember::query()
            ->where('user_id', $user->id)
            ->where('status', 'active');
        $applyPeriod($membershipQuery);
        $memberships = $membershipQuery->count();

        $enrolmentQuery = CourseEnrolment::query()
            ->where('user_id', $user->id)
            ->with('course')
            ->when($search !== '', function ($query) use ($search): void {
                $query->whereHas('course', function ($courseQuery) use ($search): void {
                    $courseQuery->where(function ($nested) use ($search): void {
                        $nested->where('title', 'like', '%'.$search.'%')
                            ->orWhere('description', 'like', '%'.$search.'%');
                    });
                });
            });
        $applyPeriod($enrolmentQuery);
        $enrolments = $enrolmentQuery
            ->latest()
            ->paginate(6, ['*'], 'learning_page')
            ->withQueryString();

        $notificationQuery = PlatformNotificationReceipt::query()
            ->with('notification')
            ->where('user_id', $user->id)
            ->when($search !== '', function ($query) use ($search): void {
                $query->whereHas('notification', function ($notificationQuery) use ($search): void {
                    $notificationQuery->where(function ($nested) use ($search): void {
                        $nested->where('title', 'like', '%'.$search.'%')
                            ->orWhere('body', 'like', '%'.$search.'%');
                    });
                });
            });
        $applyPeriod($notificationQuery);
        $notifications = $notificationQuery
            ->latest()
            ->paginate(6, ['*'], 'updates_page')
            ->withQueryString();

        if (Schema::hasTable('online_services')) {
            $serviceQuery = DB::table('online_services')
                ->whereIn('status', ['scheduled', 'live'])
                ->when($search !== '', function ($query) use ($search): void {
                    $query->where(function ($nested) use ($search): void {
                        $nested->where('title', 'like', '%'.$search.'%')
                            ->orWhere('speaker', 'like', '%'.$search.'%')
                            ->orWhere('platform', 'like', '%'.$search.'%');
                    });
                });
            $applyPeriod($serviceQuery, 'starts_at');
            $upcomingServices = $serviceQuery
                ->orderBy('starts_at')
                ->paginate(6, ['*'], 'ministry_page')
                ->withQueryString();
        } else {
            $upcomingServices = new LengthAwarePaginator([], 0, 6, 1, [
                'path' => $request->url(),
                'query' => $request->query(),
                'pageName' => 'ministry_page',
            ]);
        }

        $currentTheme = Schema::hasTable('annual_themes')
            ? DB::table('annual_themes')
                ->where('is_published', true)
                ->where('year', now()->year)
                ->first()
            : null;

        $courseStats = CourseEnrolment::query()->where('user_id', $user->id);
        $applyPeriod($courseStats);

        $unreadStats = PlatformNotificationReceipt::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at');
        $applyPeriod($unreadStats);

        $eventStats = Schema::hasTable('event_registrations')
            ? DB::table('event_registrations')->where('user_id', $user->id)
            : null;
        if ($eventStats) {
            $applyPeriod($eventStats);
        }

        $certificateStats = Schema::hasTable('course_certificates')
            ? DB::table('course_certificates')->where('user_id', $user->id)
            : null;
        if ($certificateStats) {
            $applyPeriod($certificateStats);
        }

        $prayerStats = Schema::hasTable('prayer_requests')
            ? DB::table('prayer_requests')->where('user_id', $user->id)
            : null;
        if ($prayerStats) {
            $applyPeriod($prayerStats);
        }

        $mediaStats = Schema::hasTable('media_assets')
            ? DB::table('media_assets')->where('is_published', true)
            : null;
        if ($mediaStats) {
            $applyPeriod($mediaStats);
        }

        $serviceStats = Schema::hasTable('online_services')
            ? DB::table('online_services')->whereIn('status', ['scheduled', 'live'])
            : null;
        if ($serviceStats) {
            $applyPeriod($serviceStats, 'starts_at');
        }

        return view('youth.dashboard', [
            'profile' => $profile,
            'enrolments' => $enrolments,
            'notifications' => $notifications,
            'upcomingServices' => $upcomingServices,
            'currentTheme' => $currentTheme,
            'search' => $search,
            'period' => $period,
            'stats' => [
                'life_groups' => $memberships,
                'courses' => $courseStats->count(),
                'unread' => $unreadStats->count(),
                'prayers' => $prayerStats?->count() ?? 0,
                'events' => $eventStats?->count() ?? 0,
                'certificates' => $certificateStats?->count() ?? 0,
                'media' => $mediaStats?->count() ?? 0,
                'services' => $serviceStats?->count() ?? 0,
            ],
        ]);
    }

    public function profile(Request $request): View
    {
        $profile = YouthProfile::with('organisationUnit')->where('user_id', $request->user()->id)->first();
        $units = Schema::hasTable('organisation_units')
            ? DB::table('organisation_units')->where('is_active', true)->orderBy('name')->get(['id', 'name'])
            : collect();

        return view('youth.profile', compact('profile', 'units'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'date_of_birth' => ['required', 'date', 'before:today'],
            'age_category' => ['required', Rule::in(['teen', 'youth', 'young_adult'])],
            'organisation_unit_id' => ['nullable', 'exists:organisation_units,id'],
            'school_institution' => ['nullable', 'string', 'max:190'],
            'interests' => ['nullable', 'string', 'max:2000'],
            'talents' => ['nullable', 'string', 'max:2000'],
            'skills' => ['nullable', 'string', 'max:2000'],
            'ministry_interests' => ['nullable', 'string', 'max:2000'],
            'profile_public' => ['sometimes', 'boolean'],
        ]);

        foreach (['interests', 'talents', 'skills', 'ministry_interests'] as $field) {
            $data[$field] = collect(explode(',', (string) ($data[$field] ?? '')))
                ->map(fn (string $value) => trim($value))
                ->filter()
                ->values()
                ->all();
        }

        $data['profile_public'] = $request->boolean('profile_public');

        YouthProfile::updateOrCreate(
            ['user_id' => $request->user()->id],
            $data,
        );

        return back()->with('success', 'Your youth profile has been updated.');
    }

    public function lifeGroups(Request $request): View
    {
        $userId = $request->user()->id;
        $groups = LifeGroup::query()
            ->with('organisationUnit')
            ->withCount(['members' => fn ($query) => $query->where('status', 'active')])
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(12);

        $membershipIds = LifeGroupMember::query()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->pluck('life_group_id')
            ->all();

        return view('youth.life-groups', compact('groups', 'membershipIds'));
    }

    public function joinLifeGroup(Request $request, LifeGroup $lifeGroup): RedirectResponse
    {
        abort_unless($lifeGroup->is_active, 404);

        $activeCount = $lifeGroup->members()->where('status', 'active')->count();
        if ($lifeGroup->member_limit && $activeCount >= $lifeGroup->member_limit) {
            return back()->withErrors(['life_group' => 'This Life Group has reached its member limit.']);
        }

        LifeGroupMember::updateOrCreate(
            ['life_group_id' => $lifeGroup->id, 'user_id' => $request->user()->id],
            ['role' => 'member', 'status' => 'active'],
        );

        return back()->with('success', 'You have joined '.$lifeGroup->name.'.');
    }

    public function leaveLifeGroup(Request $request, LifeGroup $lifeGroup): RedirectResponse
    {
        LifeGroupMember::query()
            ->where('life_group_id', $lifeGroup->id)
            ->where('user_id', $request->user()->id)
            ->whereIn('status', ['pending', 'active'])
            ->update(['status' => 'left']);

        return back()->with('success', 'You have left '.$lifeGroup->name.'.');
    }

    public function notifications(Request $request): View
    {
        $receipts = PlatformNotificationReceipt::query()
            ->with('notification')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return view('youth.notifications', compact('receipts'));
    }

    public function readNotification(Request $request, PlatformNotificationReceipt $receipt): RedirectResponse
    {
        abort_unless((int) $receipt->user_id === (int) $request->user()->id, 403);
        $receipt->update(['read_at' => $receipt->read_at ?: now()]);

        $url = $receipt->notification?->action_url;
        return $url ? redirect()->to($url) : back()->with('success', 'Notification marked as read.');
    }

    public function readAllNotifications(Request $request): RedirectResponse
    {
        PlatformNotificationReceipt::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function learning(Request $request): View
    {
        $profile = YouthProfile::where('user_id', $request->user()->id)->first();
        $enrolments = CourseEnrolment::query()
            ->with('course')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(12);

        $availableCourses = Course::query()
            ->where('is_published', true)
            ->when($profile?->age_category, fn ($query, $age) => $query->whereIn('age_category', [$age, 'all']))
            ->latest()
            ->limit(8)
            ->get();

        return view('youth.learning', compact('enrolments', 'availableCourses'));
    }

    public function media(): View
    {
        $media = Schema::hasTable('media_assets')
            ? MediaAsset::query()->where('is_published', true)->latest()->paginate(12)
            : new LengthAwarePaginator([], 0, 12, 1, ['path' => request()->url(), 'query' => request()->query()]);

        $services = Schema::hasTable('online_services')
            ? DB::table('online_services')
                ->whereIn('status', ['scheduled', 'live', 'completed'])
                ->orderByRaw("CASE WHEN status = 'live' THEN 0 WHEN status = 'scheduled' THEN 1 ELSE 2 END")
                ->orderByDesc('starts_at')
                ->limit(12)
                ->get()
            : collect();

        return view('youth.media', compact('media', 'services'));
    }

    public function annualTheme(): View
    {
        $themes = Schema::hasTable('annual_themes')
            ? DB::table('annual_themes')->where('is_published', true)->orderByDesc('year')->get()
            : collect();

        return view('youth.annual-theme', compact('themes'));
    }

    public function calendar(): View
    {
        $events = Schema::hasTable('events')
            ? DB::table('events')
                ->where('status', 'published')
                ->where('starts_at', '>=', now()->startOfMonth()->subMonth())
                ->orderBy('starts_at')
                ->limit(100)
                ->get()
            : collect();

        $services = Schema::hasTable('online_services')
            ? DB::table('online_services')
                ->whereIn('status', ['scheduled', 'live'])
                ->where('starts_at', '>=', now()->startOfMonth()->subMonth())
                ->orderBy('starts_at')
                ->limit(100)
                ->get()
            : collect();

        return view('youth.calendar', compact('events', 'services'));
    }
}
