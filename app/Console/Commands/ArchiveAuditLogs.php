<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AuditLog;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class ArchiveAuditLogs extends Command
{
    protected $signature = 'audit:archive {--months= : Whole months of activity to keep in the database (defaults to config audit.retention_months)}';
    protected $description = 'Move activity logs older than the retention window into compressed monthly archive files.';

    public function handle(): int
    {
        $keep = $this->option('months') !== null ? (int) $this->option('months') : (int) config('audit.retention_months', 3);
        if ($keep < 1) {
            $this->error('At least one month of activity must stay in the database.');
            return self::FAILURE;
        }

        $cutoff = CarbonImmutable::now()->startOfMonth()->subMonths($keep);
        $oldest = AuditLog::query()->where('created_at', '<', $cutoff)->min('created_at');

        if ($oldest === null) {
            $this->info('No activity older than '.$cutoff->toDateString().' to archive.');
            return self::SUCCESS;
        }

        $disk = Storage::disk('local');
        $directory = (string) config('audit.archive_directory', 'audit-archives');
        File::ensureDirectoryExists($disk->path($directory), 0700, true);

        for ($month = CarbonImmutable::parse($oldest)->startOfMonth(); $month < $cutoff; $month = $month->addMonth()) {
            [$written, $deleted] = $this->archiveMonth($month, $disk->path($directory.'/audit-logs-'.$month->format('Y-m').'.jsonl.gz'));

            if ($written > 0) {
                $this->line(sprintf('%s: archived %d, removed %d from the database.', $month->format('Y-m'), $written, $deleted));
            }
        }

        $this->info('Activity log archive complete.');

        return self::SUCCESS;
    }

    /** @return array{int, int} rows written and rows deleted */
    private function archiveMonth(CarbonImmutable $month, string $archivePath): array
    {
        $rows = AuditLog::query()
            ->with('user:id,name,email')
            ->where('created_at', '>=', $month)
            ->where('created_at', '<', $month->addMonth());

        $partPath = $archivePath.'.part';
        $gz = gzopen($partPath, 'wb9');
        if ($gz === false) {
            throw new RuntimeException("Cannot write activity archive {$partPath}.");
        }

        $written = 0;
        $lastId = 0;
        try {
            // A month archived by an earlier run is carried into the new file, so each month stays one plain gzip stream.
            if (is_file($archivePath)) {
                $existing = gzopen($archivePath, 'rb');
                if ($existing === false) {
                    throw new RuntimeException("Cannot read activity archive {$archivePath}.");
                }
                while (! gzeof($existing)) {
                    gzwrite($gz, (string) gzread($existing, 1 << 16));
                }
                gzclose($existing);
            }

            (clone $rows)->chunkById(500, function ($chunk) use ($gz, &$written, &$lastId): void {
                foreach ($chunk as $log) {
                    // Keep the actor's name and email so the archive still reads after an account is deleted.
                    gzwrite($gz, json_encode([
                        'id' => $log->id,
                        'created_at' => $log->created_at?->toIso8601String(),
                        'user_id' => $log->user_id,
                        'user_name' => $log->user?->name,
                        'user_email' => $log->user?->email,
                        'action' => $log->action,
                        'module' => $log->module,
                        'record_type' => $log->record_type,
                        'record_id' => $log->record_id,
                        'before' => $log->before,
                        'after' => $log->after,
                        'ip_address' => $log->ip_address,
                        'user_agent' => $log->user_agent,
                    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n");
                    $written++;
                    $lastId = max($lastId, (int) $log->id);
                }
            });
        } finally {
            gzclose($gz);
        }

        if ($written === 0) {
            @unlink($partPath);
            return [0, 0];
        }

        if (! rename($partPath, $archivePath)) {
            throw new RuntimeException("Cannot finalise activity archive {$archivePath}.");
        }
        @chmod($archivePath, 0600);

        // Rows are only removed once they are safely on disk, and only those that were written.
        $deleted = (clone $rows)->where('id', '<=', $lastId)->delete();

        return [$written, $deleted];
    }
}
