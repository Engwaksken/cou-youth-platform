<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ChurchLocation;
use App\Models\Content;
use App\Models\Course;
use App\Models\DonationCampaign;
use App\Models\Event;
use App\Models\LifeGroup;
use App\Models\PageCard;
use App\Models\PageSlide;
use Illuminate\Support\Facades\DB;
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
        $annualTheme = null;

        $stats = [
            'events' => 0,
            'courses' => 0,
            'life_groups' => 0,
            'church_locations' => 0,
            'donation_campaigns' => 0,
        ];

        if (Schema::hasTable('annual_themes')) {
            $themeQuery = DB::table('annual_themes');

            if (Schema::hasColumn('annual_themes', 'is_published')) {
                $themeQuery->where('is_published', true);
            }

            $annualTheme = $themeQuery
                ->orderByRaw('CASE WHEN year = ? THEN 0 ELSE 1 END', [(int) now()->year])
                ->orderByDesc('year')
                ->first();
        }

        if (Schema::hasTable('contents')) {
            $query = Content::query();

            if (Schema::hasColumn('contents', 'status')) {
                $query->where('status', 'published');
            }

            if (Schema::hasColumn('contents', 'type')) {
                $query->whereIn('type', ['news', 'announcement', 'devotion', 'opportunity']);
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

            $upcomingEvents = $query->limit(6)->get();

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
            $churchLocations = ChurchLocation::query()
                ->with('organisationUnit')
                ->latest()
                ->limit(6)
                ->get();

            $stats['church_locations'] = ChurchLocation::count();
        }

        if (Schema::hasTable('donation_campaigns')) {
            $query = DonationCampaign::query()
                ->withSum([
                    'donations as amount_raised' => fn ($q) => $q->whereIn('status', [
                        'paid', 'completed', 'successful',
                    ]),
                ], 'amount');

            if (Schema::hasColumn('donation_campaigns', 'status')) {
                $query->whereIn('status', ['published', 'active', 'open']);
            }

            $donationCampaigns = $query->latest()->limit(3)->get();
            $stats['donation_campaigns'] = $donationCampaigns->count();
        }

        $slides = collect();
        $cards = collect();

        if (Schema::hasTable('page_slides')) {
            $slides = PageSlide::query()->forPage('home')->active()->ordered()->get();
        }

        if (Schema::hasTable('page_cards')) {
            $cards = PageCard::query()->forPage('home')->active()->ordered()->get();
        }

        return view('welcome', compact(
            'latestNews',
            'upcomingEvents',
            'courses',
            'churchLocations',
            'donationCampaigns',
            'annualTheme',
            'stats',
            'slides',
            'cards',
        ));
    }
}
