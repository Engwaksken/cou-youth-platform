<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/** Stores, replaces and removes user profile photos on the public disk. */
final class UserProfilePhoto
{
    /** Validation rules shared by every form that accepts a profile photo. */
    public static function rules(): array
    {
        return [
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=4000,max_height=4000'],
            'remove_photo' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Update the user with validated input, storing an uploaded photo or
     * clearing it on request. The old file is deleted only once the new
     * value has been saved; a failed save removes the new upload instead.
     *
     * @param  array<string, mixed>  $data  validated input, may include photo / remove_photo
     */
    public static function updateUser(User $user, array $data): void
    {
        $photo = $data['photo'] ?? null;
        $remove = (bool) ($data['remove_photo'] ?? false);
        unset($data['photo'], $data['remove_photo']);

        $previous = $user->profile_photo;
        if ($photo instanceof UploadedFile) {
            $data['profile_photo'] = $photo->store('profile-photos', 'public');
        } elseif ($remove) {
            $data['profile_photo'] = null;
        }

        try {
            $user->update($data);
        } catch (\Throwable $exception) {
            if (! empty($data['profile_photo'])) {
                Storage::disk('public')->delete($data['profile_photo']);
            }
            throw $exception;
        }

        if (array_key_exists('profile_photo', $data) && $previous && $previous !== $data['profile_photo']) {
            Storage::disk('public')->delete($previous);
        }
    }

    public static function delete(User $user): void
    {
        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
        }
    }
}
