<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ChurchLocation;
use App\Models\Content;
use App\Models\Course;
use App\Models\DonationCampaign;
use App\Models\Event;
use App\Models\LifeGroup;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $latestNews = collect();
        $upcomingEvents = collect();
        $courses = collect();
        $churchLocations = collect();
        $donationCampaigns = collect();

        $stats = [
            'events' => 0,
            'courses' => 0,
            'life_groups' => 0,
            'church_locations' => 0,
            'donation_campaigns' => 0,
        ];

        if (Schema::hasTable('contents')) {
            $query = Content::query();

            if (Schema::hasColumn('contents', 'status')) {
                $query->where('status', 'published');
            }

            if (Schema::hasColumn('contents', 'type')) {
                $query->whereIn('type', [
                    'news',
                    'announcement',
                    'devotion',
                    'opportunity',
                ]);
            }

            if (Schema::hasColumn('contents', 'published_at')) {
                $query->orderByDesc('published_at');
            } else {
                $query->latest();
            }

            $latestNews = $query->limit(6)->get();
        }

        if (Schema::hasTable('events')) {
            $query = Event::query();

            if (Schema::hasColumn('events', 'status')) {
                $query->where('status', 'published');
            }

            if (Schema::hasColumn('events', 'starts_at')) {
                $query->where('starts_at', '>=', now())->orderBy('starts_at');
            } elseif (Schema::hasColumn('events', 'start_date')) {
                $query->whereDate('start_date', '>=', now()->toDateString())->orderBy('start_date');
            } elseif (Schema::hasColumn('events', 'event_date')) {
                $query->whereDate('event_date', '>=', now()->toDateString())->orderBy('event_date');
            } else {
                $query->latest();
            }

            $upcomingEvents = $query
                ->limit(6)
                ->get()
                ->map(function (Event $event): Event {
                    // The existing public Blade supports start_date/event_date.
                    // Expose the canonical starts_at value without duplicating database columns.
                    if ($event->starts_at && empty($event->start_date)) {
                        $event->setAttribute('start_date', $event->starts_at);
                    }

                    return $event;
                });

            $stats['events'] = Event::query()
                ->when(
                    Schema::hasColumn('events', 'status'),
                    fn ($q) => $q->where('status', 'published')
                )
                ->count();
        }

        if (Schema::hasTable('courses')) {
            $query = Course::query();

            if (Schema::hasColumn('courses', 'is_published')) {
                $query->where('is_published', true);
            } elseif (Schema::hasColumn('courses', 'is_active')) {
                $query->where('is_active', true);
            } elseif (Schema::hasColumn('courses', 'status')) {
                $query->where('status', 'published');
            }

            $courses = $query->latest()->limit(6)->get();

            $stats['courses'] = Course::query()
                ->when(
                    Schema::hasColumn('courses', 'is_published'),
                    fn ($q) => $q->where('is_published', true)
                )
                ->count();
        }

        if (Schema::hasTable('life_groups')) {
            $stats['life_groups'] = LifeGroup::count();
        }

        if (Schema::hasTable('church_locations')) {
            $query = ChurchLocation::query()->with('organisationUnit');

            $churchLocations = $query
                ->latest()
                ->limit(6)
                ->get()
                ->map(function (ChurchLocation $church): ChurchLocation {
                    $unit = $church->organisationUnit;

                    // Normalize fields expected by the public Blade while retaining
                    // the current normalized church_locations schema.
                    $church->setAttribute('name', $unit?->name ?? 'Church of Uganda');
                    $church->setAttribute('phone', $church->contact_phone ?? $unit?->phone);
                    $church->setAttribute('email', $church->contact_email ?? $unit?->email);
                    $church->setAttribute('location', $church->address ?? $unit?->address);

                    return $church;
                });

            $stats['church_locations'] = ChurchLocation::count();
        }

        if (Schema::hasTable('donation_campaigns')) {
            $query = DonationCampaign::query()
                ->withSum([
                    'donations as amount_raised' => fn ($q) => $q->whereIn('status', [
                        'paid',
                        'completed',
                        'successful',
                    ]),
                ], 'amount');

            if (Schema::hasColumn('donation_campaigns', 'status')) {
                $query->whereIn('status', ['published', 'active', 'open']);
            }

            if (Schema::hasColumn('donation_campaigns', 'starts_at')) {
                $query->where(function ($q): void {
                    $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                });
            }

            if (Schema::hasColumn('donation_campaigns', 'ends_at')) {
                $query->where(function ($q): void {
                    $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                });
            }

            $donationCampaigns = $query->latest()->limit(3)->get();
            $stats['donation_campaigns'] = $donationCampaigns->count();
        }

        return view('welcome', compact(
            'latestNews',
            'upcomingEvents',
            'courses',
            'churchLocations',
            'donationCampaigns',
            'stats',
        ));
    }
}
