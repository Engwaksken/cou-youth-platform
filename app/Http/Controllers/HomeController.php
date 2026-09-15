<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ChurchLocation;
use App\Models\Content;
use App\Models\Course;
use App\Models\Event;
use App\Models\LifeGroup;
use Illuminate\Support\Collection;
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

        $stats = [
            'events' => 0,
            'courses' => 0,
            'life_groups' => 0,
            'church_locations' => 0,
        ];

        /*
        |--------------------------------------------------------------------------
        | News / Announcements
        |--------------------------------------------------------------------------
        */

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

            $latestNews = $query
                ->latest()
                ->limit(6)
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | Events
        |--------------------------------------------------------------------------
        */

        if (Schema::hasTable('events')) {
            $query = Event::query();

            if (Schema::hasColumn('events', 'start_date')) {
                $query
                    ->whereDate('start_date', '>=', now()->toDateString())
                    ->orderBy('start_date');
            } elseif (Schema::hasColumn('events', 'event_date')) {
                $query
                    ->whereDate('event_date', '>=', now()->toDateString())
                    ->orderBy('event_date');
            } else {
                $query->latest();
            }

            $upcomingEvents = $query
                ->limit(6)
                ->get();

            $stats['events'] = Event::count();
        }

        /*
        |--------------------------------------------------------------------------
        | Discipleship Courses
        |--------------------------------------------------------------------------
        */

        if (Schema::hasTable('courses')) {
            $query = Course::query();

            if (Schema::hasColumn('courses', 'is_active')) {
                $query->where('is_active', true);
            }

            if (Schema::hasColumn('courses', 'status')) {
                $query->where('status', 'published');
            }

            $courses = $query
                ->latest()
                ->limit(6)
                ->get();

            $stats['courses'] = Course::count();
        }

        /*
        |--------------------------------------------------------------------------
        | Life Groups
        |--------------------------------------------------------------------------
        */

        if (Schema::hasTable('life_groups')) {
            $stats['life_groups'] = LifeGroup::count();
        }

        /*
        |--------------------------------------------------------------------------
        | Church Locations
        |--------------------------------------------------------------------------
        */

        if (Schema::hasTable('church_locations')) {
            $query = ChurchLocation::query();

            if (Schema::hasColumn('church_locations', 'is_active')) {
                $query->where('is_active', true);
            }

            $churchLocations = $query
                ->latest()
                ->limit(6)
                ->get();

            $stats['church_locations'] = ChurchLocation::count();
        }

        return view('welcome', compact(
            'latestNews',
            'upcomingEvents',
            'courses',
            'churchLocations',
            'stats',
        ));
    }
}