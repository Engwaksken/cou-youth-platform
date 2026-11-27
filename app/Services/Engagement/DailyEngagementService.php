<?php

declare(strict_types=1);

namespace App\Services\Engagement;

use App\Models\User;
use App\Models\YouthCheckin;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;

/**
 * The small daily rituals on the youth home page: checking in, keeping a
 * streak going, and the verse of the day. All "days" are local calendar days
 * in the configured engagement timezone.
 */
final class DailyEngagementService
{
    /** Streak lengths worth celebrating, in days. */
    private const MILESTONES = [3, 7, 14, 30, 50, 100, 200, 365];

    public function now(): CarbonImmutable
    {
        return CarbonImmutable::now($this->timezone());
    }

    public function today(): CarbonImmutable
    {
        return $this->now()->startOfDay();
    }

    /** Records today's check-in. Returns false when the member had already checked in today. */
    public function checkIn(User $user): bool
    {
        try {
            $checkin = YouthCheckin::query()->firstOrCreate([
                'user_id' => $user->getKey(),
                'checked_in_on' => $this->today()->toDateString(),
            ]);
        } catch (QueryException) {
            // A double tap can race past firstOrCreate; the unique index keeps one row.
            return false;
        }

        return $checkin->wasRecentlyCreated;
    }

    /**
     * @return array{current:int,best:int,total:int,checked_in_today:bool,next_milestone:?int,week:list<array{date:string,label:string,done:bool,today:bool,future:bool}>}
     */
    public function streak(User $user): array
    {
        $today = $this->today();

        $days = YouthCheckin::query()
            ->where('user_id', $user->getKey())
            ->orderBy('checked_in_on')
            ->get(['checked_in_on'])
            ->map(fn (YouthCheckin $checkin): string => $checkin->checked_in_on->toDateString())
            ->unique()
            ->values();

        $done = array_flip($days->all());
        $checkedInToday = isset($done[$today->toDateString()]);

        // A streak stays alive until the end of the first day without a check-in,
        // so yesterday's run still counts while today is not over.
        $current = 0;
        $cursor = $checkedInToday ? $today : $today->subDay();
        while (isset($done[$cursor->toDateString()])) {
            $current++;
            $cursor = $cursor->subDay();
        }

        $best = 0;
        $run = 0;
        $previous = null;
        foreach ($days as $day) {
            $date = CarbonImmutable::parse($day, $this->timezone());
            $run = ($previous !== null && $previous->addDay()->isSameDay($date)) ? $run + 1 : 1;
            $best = max($best, $run);
            $previous = $date;
        }

        $week = [];
        $monday = $today->startOfWeek(CarbonImmutable::MONDAY);
        for ($i = 0; $i < 7; $i++) {
            $date = $monday->addDays($i);
            $week[] = [
                'date' => $date->toDateString(),
                'label' => $date->format('D'),
                'done' => isset($done[$date->toDateString()]),
                'today' => $date->isSameDay($today),
                'future' => $date->greaterThan($today),
            ];
        }

        $next = null;
        foreach (self::MILESTONES as $milestone) {
            if ($milestone > $current) {
                $next = $milestone;
                break;
            }
        }

        return [
            'current' => $current,
            'best' => max($best, $current),
            'total' => $days->count(),
            'checked_in_today' => $checkedInToday,
            'next_milestone' => $next,
            'week' => $week,
        ];
    }

    /** @return array{ref:string,text:string,translation:string}|null */
    public function verseOfTheDay(?CarbonImmutable $day = null): ?array
    {
        $verses = array_values(array_filter(
            (array) config('cou_youth.daily_verses', []),
            fn ($verse): bool => is_array($verse) && ! empty($verse['ref']) && ! empty($verse['text']),
        ));

        if ($verses === []) {
            return null;
        }

        $day ??= $this->today();
        $verse = $verses[$day->dayOfYear % count($verses)];

        return [
            'ref' => (string) $verse['ref'],
            'text' => (string) $verse['text'],
            'translation' => (string) config('cou_youth.daily_verses_translation', ''),
        ];
    }

    public function greeting(): string
    {
        $hour = $this->now()->hour;

        return match (true) {
            $hour < 5 => 'Up late',
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default => 'Good evening',
        };
    }

    private function timezone(): string
    {
        $timezone = (string) config('cou_youth.engagement_timezone', 'Africa/Kampala');

        return in_array($timezone, timezone_identifiers_list(), true) ? $timezone : 'UTC';
    }
}
