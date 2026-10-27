<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ActivityLogController extends Controller
{
    private const ARCHIVE_PATTERN = '/^audit-logs-\d{4}-\d{2}\.jsonl\.gz$/';

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => 'nullable|string|max:120',
            'action' => 'nullable|string|max:40',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);

        $logs = AuditLog::query()
            ->with('user:id,name,email')
            ->when($filters['q'] ?? null, function ($query, string $term): void {
                $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term).'%';
                $query->where(fn ($inner) => $inner
                    ->where('module', 'like', $like)
                    ->orWhere('ip_address', 'like', $like)
                    ->orWhereHas('user', fn ($user) => $user->where('name', 'like', $like)->orWhere('email', 'like', $like)));
            })
            ->when($filters['action'] ?? null, fn ($query, string $action) => $query->where('action', $action))
            ->when($filters['from'] ?? null, fn ($query, string $from) => $query->where('created_at', '>=', Carbon::parse($from)->startOfDay()))
            ->when($filters['to'] ?? null, fn ($query, string $to) => $query->where('created_at', '<=', Carbon::parse($to)->endOfDay()))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.activity-logs.index', [
            'logs' => $logs,
            'filters' => $filters,
            'actions' => AuditLog::query()->distinct()->orderBy('action')->pluck('action'),
            'archives' => $this->archives(),
            'retentionMonths' => (int) config('audit.retention_months', 3),
            'stats' => [
                'total' => AuditLog::query()->count(),
                'today' => AuditLog::query()->where('created_at', '>=', now()->startOfDay())->count(),
                'failed_logins' => AuditLog::query()->where('action', 'login_failed')->where('created_at', '>=', now()->subDays(7))->count(),
            ],
        ]);
    }

    public function downloadArchive(string $file): StreamedResponse
    {
        abort_unless(preg_match(self::ARCHIVE_PATTERN, $file) === 1, 404);

        $path = $this->archiveDirectory().'/'.$file;
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, $file, ['Content-Type' => 'application/gzip']);
    }

    /** @return list<array{name: string, month: string, size: int}> */
    private function archives(): array
    {
        $disk = Storage::disk('local');

        return collect($disk->files($this->archiveDirectory()))
            ->map(fn (string $path): string => basename($path))
            ->filter(fn (string $name): bool => preg_match(self::ARCHIVE_PATTERN, $name) === 1)
            ->sortDesc()
            ->map(fn (string $name): array => [
                'name' => $name,
                'month' => Carbon::createFromFormat('Y-m', substr($name, 11, 7))->format('F Y'),
                'size' => $disk->size($this->archiveDirectory().'/'.$name),
            ])
            ->values()
            ->all();
    }

    private function archiveDirectory(): string
    {
        return (string) config('audit.archive_directory', 'audit-archives');
    }
}
