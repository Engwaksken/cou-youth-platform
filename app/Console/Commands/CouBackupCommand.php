<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class CouBackupCommand extends Command
{
    protected $signature = 'cou:backup {--database-only : Back up only the database}';
    protected $description = 'Create a timestamped Church of Uganda Youth Platform backup.';

    public function handle(): int
    {
        $diskPath = storage_path('app/backups/'.now()->format('Y-m-d_His'));
        File::ensureDirectoryExists($diskPath);

        $connection = config('database.default');
        $db = config("database.connections.$connection");

        if (($db['driver'] ?? null) !== 'mysql') {
            $this->error('Automated command currently supports MySQL. Configure provider-native backup for other drivers.');
            return self::FAILURE;
        }

        $dump = $diskPath.'/database.sql';
        $args = ['mysqldump', '-h', (string) $db['host'], '-P', (string) $db['port'], '-u', (string) $db['username']];
        if (! empty($db['password'])) {
            $args[] = '--password='.$db['password'];
        }
        $args[] = (string) $db['database'];

        $process = new Process($args);
        $process->setTimeout(600);
        $process->run();
        if (! $process->isSuccessful()) {
            $this->error('Database backup failed.');
            return self::FAILURE;
        }
        File::put($dump, $process->getOutput());

        if (! $this->option('database-only') && File::exists(storage_path('app/public'))) {
            $archive = $diskPath.'/uploads.tar.gz';
            $tar = new Process(['tar', '-czf', $archive, '-C', storage_path('app'), 'public']);
            $tar->setTimeout(600);
            $tar->run();
            if (! $tar->isSuccessful()) {
                $this->warn('Database backup succeeded, but upload archive failed.');
            }
        }

        File::put($diskPath.'/manifest.json', json_encode([
            'created_at' => now()->toIso8601String(),
            'release' => config('release.version'),
            'build' => config('release.build'),
            'database' => $db['database'] ?? null,
        ], JSON_PRETTY_PRINT));

        $this->info('Backup created: '.$diskPath);
        return self::SUCCESS;
    }
}
