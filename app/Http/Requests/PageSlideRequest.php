<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PageSlideRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Relies on route ['auth', 'cms.access'] middleware; no Gate/policy exists.
        return (bool) $this->user();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $mediaFile = ['nullable', 'file', 'max:10240'];

        if ($this->input('media_type') === 'video') {
            $mediaFile[] = 'mimetypes:video/mp4,video/webm';
        } else {
            $mediaFile[] = 'mimes:jpg,jpeg,png,webp';
        }

        return [
            'page' => 'required|string|max:50',
            'title' => 'nullable|string|max:180',
            'subtitle' => 'nullable|string|max:255',
            'media_type' => 'required|in:image,video',
            'media_file' => $mediaFile,
            'link_url' => ['nullable', 'string', 'max:255', 'regex:/^(\/|https?:\/\/[^\s]+)$/'],
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }
}
