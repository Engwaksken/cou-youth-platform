<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Models\PlatformNotification;

final class PlatformUpdateNotificationService
{
    public function __construct(private TargetedNotificationService $targeted) {}

    public function notifyYouth(
        string $title,
        string $body,
        ?string $actionUrl = null,
        ?int $organisationUnitId = null,
        array|string|null $ageCategories = null,
        ?int $createdBy = null,
    ): void {
        $categories = is_array($ageCategories)
            ? array_values(array_unique(array_filter($ageCategories)))
            : [$ageCategories ?: 'all'];

        if ($categories === [] || count($categories) >= 3) {
            $categories = ['all'];
        }

        foreach ($categories as $category) {
            $category = in_array($category, ['teen', 'youth', 'young_adult', 'all'], true)
                ? $category
                : 'all';

            $notification = PlatformNotification::create([
                'title' => $title,
                'body' => $body,
                'channel' => 'push',
                'organisation_unit_id' => $organisationUnitId,
                'age_category' => $category,
                'action_url' => $actionUrl,
                'created_by' => $createdBy,
                'is_active' => true,
            ]);

            $this->targeted->dispatch($notification);
        }
    }
}
