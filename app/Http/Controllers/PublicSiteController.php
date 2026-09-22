<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ChurchLocation;
use App\Models\Content;
use App\Models\Course;
use App\Models\DonationCampaign;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PublicSiteController extends Controller
{
    public function news(Request $request): View
    {
        $query = Content::query()->where('status', 'published');
        if ($request->filled('q')) {
            $search = trim((string) $request->query('q'));
            $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")
                ->orWhere('summary', 'like', "%{$search}%"));
        }

        return view('public.news', [
            'items' => $query->latest('published_at')->paginate(12)->withQueryString(),
        ]);
    }

    public function events(Request $request): View
    {
        $query = Event::query()->where('status', 'published');
        if ($request->filled('q')) {
            $search = trim((string) $request->query('q'));
            $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")
                ->orWhere('venue', 'like', "%{$search}%"));
        }

        return view('public.events', [
            'items' => $query->orderBy('starts_at')->paginate(12)->withQueryString(),
        ]);
    }

    public function courses(Request $request): View
    {
        $query = Course::query()->where('is_published', true)->withCount('lessons');
        if ($request->filled('q')) {
            $search = trim((string) $request->query('q'));
            $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%"));
        }

        return view('public.courses', [
            'items' => $query->latest()->paginate(12)->withQueryString(),
        ]);
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

        return view('public.churches', [
            'items' => $query->latest()->paginate(12)->withQueryString(),
        ]);
    }

    public function donate(): View
    {
        $campaigns = DonationCampaign::query()
            ->whereIn('status', ['published', 'active', 'open'])
            ->withSum(['donations as amount_raised' => fn ($q) => $q->whereIn('status', ['successful', 'paid', 'completed'])], 'amount')
            ->latest()
            ->paginate(9);

        return view('public.donate', compact('campaigns'));
    }

    public function about(): View
    {
        return view('public.about');
    }

    public function registerByToken(string $token)
    {
        $event = Event::where('qr_token', $token)->firstOrFail();
        if ($event->allow_external_registration && $event->external_registration_url) {
            return redirect()->away($event->external_registration_url);
        }
        return view('public.event-register', compact('event'));
    }

    public function storeRegistrationByToken(Request $request, string $token)
    {
        $event = Event::where('qr_token', $token)->firstOrFail();
        if ($event->allow_external_registration && $event->external_registration_url) {
            return redirect()->away($event->external_registration_url);
        }
        $data = $request->validate([
            'name' => 'required|max:160',
            'phone' => 'nullable|max:40',
            'email' => 'nullable|email|max:200',
        ]);
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
