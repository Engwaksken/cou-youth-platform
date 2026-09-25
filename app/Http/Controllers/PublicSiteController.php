<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRegistrationRequest;
use App\Models\ChurchLocation;
use App\Models\Content;
use App\Models\Course;
use App\Models\DonationCampaign;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\LifeGroup;
use App\Models\PageCard;
use App\Models\PageSlide;
use App\Models\PrayerRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class PublicSiteController extends Controller
{
    /** @return array{slides: Collection<int, PageSlide>, cards: Collection<int, PageCard>} */
    private function pageContent(string $page): array
    {
        $slides = collect();
        $cards = collect();

        if (Schema::hasTable('page_slides')) {
            $slides = PageSlide::query()->forPage($page)->active()->ordered()->get();
        }

        if (Schema::hasTable('page_cards')) {
            $cards = PageCard::query()->forPage($page)->active()->ordered()->get();
        }

        return compact('slides', 'cards');
    }

    public function news(Request $request): View
    {
        $query = Content::query()->where('status', 'published');
        if ($request->filled('q')) {
            $search = trim((string) $request->query('q'));
            $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")
                ->orWhere('summary', 'like', "%{$search}%"));
        }

        return view('public.news', array_merge(
            ['items' => $query->latest('published_at')->paginate(12)->withQueryString()],
            $this->pageContent('news'),
        ));
    }

    public function events(Request $request): View
    {
        $query = Event::query()->where('status', 'published');
        if ($request->filled('q')) {
            $search = trim((string) $request->query('q'));
            $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")
                ->orWhere('venue', 'like', "%{$search}%"));
        }

        return view('public.events', array_merge(
            ['items' => $query->orderBy('starts_at')->paginate(12)->withQueryString()],
            $this->pageContent('events'),
        ));
    }

    public function eventShow(Event $event): View
    {
        abort_unless($event->status === 'published', 404);

        $event->loadMissing('organisationUnit');
        $relatedEvents = Event::query()
            ->where('status', 'published')
            ->whereKeyNot($event->getKey())
            ->when($event->category, fn ($q) => $q->where('category', $event->category))
            ->orderBy('starts_at')
            ->limit(3)
            ->get();

        return view('public.event-show', compact('event', 'relatedEvents'));
    }

    public function courses(Request $request): View
    {
        $query = Course::query()->where('is_published', true)->withCount('lessons');
        if ($request->filled('q')) {
            $search = trim((string) $request->query('q'));
            $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%"));
        }

        return view('public.courses', array_merge(
            ['items' => $query->latest()->paginate(12)->withQueryString()],
            $this->pageContent('courses'),
        ));
    }

    public function courseShow(Course $course): View
    {
        abort_unless((bool) $course->is_published, 404);

        $course->load('lessons');
        $relatedCourses = Course::query()
            ->where('is_published', true)
            ->whereKeyNot($course->getKey())
            ->latest()
            ->limit(3)
            ->get();

        return view('public.course-show', compact('course', 'relatedCourses'));
    }

    public function lifeGroups(Request $request): View
    {
        $query = LifeGroup::query()
            ->where('is_active', true)
            ->with('organisationUnit')
            ->withCount('members');

        if ($request->filled('q')) {
            $search = trim((string) $request->query('q'));
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('meeting_location', 'like', "%{$search}%")
                    ->orWhereHas('organisationUnit', fn ($unit) => $unit->where('name', 'like', "%{$search}%"));
            });
        }

        return view('public.life-groups', array_merge(
            ['items' => $query->orderBy('name')->paginate(12)->withQueryString()],
            $this->pageContent('life-groups'),
        ));
    }

    public function churches(Request $request): View
    {
        $query = ChurchLocation::query()->with('organisationUnit');
        if ($request->filled('q')) {
            $search = trim((string) $request->query('q'));
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%")
                ->orWhereHas('organisationUnit', fn ($unit) => $unit->where('name', 'like', "%{$search}%")));
        }

        return view('public.churches', array_merge(
            ['items' => $query->latest()->paginate(12)->withQueryString()],
            $this->pageContent('churches'),
        ));
    }

    public function donate(): View
    {
        $campaigns = DonationCampaign::query()
            ->whereIn('status', ['published', 'active', 'open'])
            ->withSum(['donations as amount_raised' => fn ($q) => $q->whereIn('status', ['successful', 'paid', 'completed'])], 'amount')
            ->latest()
            ->paginate(9);

        return view('public.donate', array_merge(
            compact('campaigns'),
            $this->pageContent('donate'),
        ));
    }

    public function prayer(Request $request): View
    {
        $requests = PrayerRequest::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('public.prayer', compact('requests'));
    }

    public function storePrayer(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'visibility' => ['required', Rule::in(['private', 'pastoral_team', 'public_anonymous'])],
        ]);

        PrayerRequest::query()->create([
            'user_id' => $request->user()->id,
            'organisation_unit_id' => null,
            'subject' => trim($data['subject']),
            'message' => trim($data['message']),
            'visibility' => $data['visibility'],
            'status' => 'submitted',
            'requires_safeguarding_review' => false,
            'assigned_to' => null,
        ]);

        return redirect()->route('public.prayer')->with('success', 'Your prayer request has been submitted securely.');
    }

    public function about(): View
    {
        return view('public.about', $this->pageContent('about'));
    }

    public function registerByToken(string $token)
    {
        $event = Event::where('qr_token', $token)->firstOrFail();
        if ($event->allow_external_registration && $event->external_registration_url) {
            return redirect()->away($event->external_registration_url);
        }
        return view('public.event-register', compact('event'));
    }

    public function storeRegistrationByToken(StoreEventRegistrationRequest $request, string $token)
    {
        $event = Event::where('qr_token', $token)->firstOrFail();
        if ($event->allow_external_registration && $event->external_registration_url) {
            return redirect()->away($event->external_registration_url);
        }
        $data = $request->validated();
        EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $request->user()?->id,
            'name' => $data['name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'status' => 'registered',
        ]);
        return back()->with('success', 'Registration successful.');
    }
}
