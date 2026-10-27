<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_devices')) {
            return;
        }

        $key = config('app.key');
        if (! is_string($key) || trim($key) === '') {
            throw new RuntimeException('APP_KEY is required to migrate user device push tokens. Configure a stable APP_KEY before running this migration; do not generate a new key between runs.');
        }

        // Validate the full dataset before making any schema or data changes.
        $rows = DB::table('user_devices')->orderBy('id')->get(['id', 'user_id', 'push_token']);
        $seen = [];
        $duplicates = [];

        foreach ($rows as $row) {
            $token = $this->plaintextToken($row->push_token);
            if ($token === null || $token === '') {
                continue;
            }

            $key = (string) $row->user_id . "\0" . hash('sha256', $token);
            if (isset($seen[$key])) {
                $duplicates[] = (string) $row->id;
                continue;
            }
            $seen[$key] = (string) $row->id;
        }

        if ($duplicates !== []) {
            throw new RuntimeException(
                'Duplicate push tokens exist for the same user (device row IDs: '.implode(', ', $duplicates).'). '
                .'Resolve these records manually, keeping or changing rows as appropriate, then rerun this migration. '
                .'No token values or hashes are included; no rows were deleted.'
            );
        }

        $addedHashColumn = ! Schema::hasColumn('user_devices', 'push_token_hash');
        if ($addedHashColumn) {
            Schema::table('user_devices', function (Blueprint $table): void {
                $table->char('push_token_hash', 64)->nullable();
            });
        }

        foreach (Schema::getIndexes('user_devices') as $index) {
            if (($index['unique'] ?? false) && ($index['columns'] ?? []) === ['user_id', 'push_token']) {
                $this->keepUserIdIndexed($index['name']);
                Schema::table('user_devices', function (Blueprint $table) use ($index): void {
                    $table->dropUnique($index['name']);
                });
            }
        }

        foreach ($rows as $row) {
            $token = $this->plaintextToken($row->push_token);
            DB::table('user_devices')->where('id', $row->id)->update([
                'push_token' => ($token === null || $token === '') ? $row->push_token : ($this->encryptedToken($row->push_token) ? $row->push_token : Crypt::encryptString($token)),
                'push_token_hash' => ($token === null || $token === '') ? null : hash('sha256', $token),
            ]);
        }

        $hasHashUnique = false;
        foreach (Schema::getIndexes('user_devices') as $index) {
            if (($index['unique'] ?? false) && ($index['columns'] ?? []) === ['user_id', 'push_token_hash']) {
                $hasHashUnique = true;
                break;
            }
        }
        if (! $hasHashUnique) {
            Schema::table('user_devices', function (Blueprint $table): void {
                $table->unique(['user_id', 'push_token_hash']);
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('user_devices') || ! Schema::hasColumn('user_devices', 'push_token_hash')) {
            return;
        }

        foreach (DB::table('user_devices')->orderBy('id')->get(['id', 'push_token']) as $row) {
            $token = $this->plaintextToken($row->push_token);
            if ($token !== null && $token !== '') {
                DB::table('user_devices')->where('id', $row->id)->update(['push_token' => $token]);
            }
        }

        foreach (Schema::getIndexes('user_devices') as $index) {
            if (($index['unique'] ?? false) && ($index['columns'] ?? []) === ['user_id', 'push_token_hash']) {
                $this->keepUserIdIndexed($index['name']);
                Schema::table('user_devices', function (Blueprint $table) use ($index): void {
                    $table->dropUnique($index['name']);
                });
            }
        }

        // The hash column is also part of the original fresh-install schema. Keep it
        // so rollback never removes a column this migration did not create.
    }

    /**
     * MySQL backs the user_id foreign key with the first index that starts with user_id,
     * and refuses to drop that index. Add a plain one first when no other index covers it.
     */
    private function keepUserIdIndexed(string $droppingIndex): void
    {
        foreach (Schema::getIndexes('user_devices') as $index) {
            if ($index['name'] !== $droppingIndex && ($index['columns'][0] ?? null) === 'user_id') {
                return;
            }
        }

        Schema::table('user_devices', function (Blueprint $table): void {
            $table->index('user_id');
        });
    }

    private function plaintextToken(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (Throwable $exception) {
            if ($this->resemblesCiphertext($value)) {
                throw new RuntimeException('A push_token contains malformed or undecryptable ciphertext. No schema or row changes were made.', 0, $exception);
            }

            return $value;
        }
    }

    private function encryptedToken(?string $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        try {
            Crypt::decryptString($value);

            return true;
        } catch (Throwable $exception) {
            if ($this->resemblesCiphertext($value)) {
                throw new RuntimeException('A push_token contains malformed or undecryptable ciphertext. No schema or row changes were made.', 0, $exception);
            }

            return false;
        }
    }

    private function resemblesCiphertext(string $value): bool
    {
        $decoded = base64_decode($value, true);
        if ($decoded === false) {
            return false;
        }

        $payload = json_decode($decoded, true);

        return is_array($payload) && (array_key_exists('iv', $payload) || array_key_exists('value', $payload) || array_key_exists('mac', $payload) || array_key_exists('tag', $payload));
    }
};
