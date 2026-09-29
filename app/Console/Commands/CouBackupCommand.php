<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

final class CouBackupCommand extends Command
{
    protected $signature = 'cou:backup {--database-only : Back up only the database}';
    protected $description = 'Create a timestamped Church of Uganda Youth Platform backup.';

    public function handle(): int
    {
        $diskPath = storage_path('app/backups/'.now()->format('Y-m-d_His'));
        File::ensureDirectoryExists($diskPath, 0700, true);

        $connection = (string) config('database.default');
        $db = config("database.connections.$connection");

        if (($db['driver'] ?? null) !== 'mysql') {
            $this->error('Automated command currently supports MySQL. Configure provider-native backup for other drivers.');
            return self::FAILURE;
        }

        $dump = $diskPath.'/database.sql';
        $args = [
            'mysqldump',
            '--single-transaction',
            '--quick',
            '--routines',
            '--triggers',
            '-h', (string) $db['host'],
            '-P', (string) $db['port'],
            '-u', (string) $db['username'],
            (string) $db['database'],
        ];

        // MYSQL_PWD avoids exposing the password in the process command line.
        // Backups are kept beneath storage/ and must not be web-accessible.
        $environment = [];
        if (! empty($db['password'])) {
            $environment['MYSQL_PWD'] = (string) $db['password'];
        }

        $process = new Process($args, null, $environment ?: null);
        $process->setTimeout(600);
        $process->run();
        if (! $process->isSuccessful()) {
            $this->error('Database backup failed.');
            File::deleteDirectory($diskPath);
            return self::FAILURE;
        }
        File::put($dump, $process->getOutput());
        @chmod($dump, 0600);

        if (! $this->option('database-only') && File::exists(storage_path('app/public'))) {
            $archive = $diskPath.'/uploads.tar.gz';
            $tar = new Process(['tar', '-czf', $archive, '-C', storage_path('app'), 'public']);
            $tar->setTimeout(600);
            $tar->run();
            if (! $tar->isSuccessful()) {
                $this->warn('Database backup succeeded, but upload archive failed.');
            } else {
                @chmod($archive, 0600);
            }
        }

        File::put($diskPath.'/manifest.json', json_encode([
            'created_at' => now()->toIso8601String(),
            'release' => config('release.version'),
            'build' => config('release.build'),
            'database' => $db['database'] ?? null,
        ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
        @chmod($diskPath.'/manifest.json', 0600);

        $this->info('Backup created: '.$diskPath);
        return self::SUCCESS;
    }
}
