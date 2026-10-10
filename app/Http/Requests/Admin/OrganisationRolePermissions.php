<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

final class OrganisationRolePermissions
{
    /** Exact admin route names available in routes/web.php; deliberately no wildcard grants. */
    public static function allowed(): array
    {
        return [
            'admin.dashboard', 'admin.roles.index', 'admin.roles.store', 'admin.roles.update', 'admin.roles.destroy',
            'admin.users.index', 'admin.users.store', 'admin.users.update', 'admin.users.destroy',
            'admin.user-organisation-roles.index', 'admin.user-organisation-roles.store', 'admin.user-organisation-roles.show', 'admin.user-organisation-roles.update', 'admin.user-organisation-roles.destroy',
            'admin.organisation-units.index', 'admin.organisation-units.store', 'admin.organisation-units.update', 'admin.organisation-units.destroy',
            'admin.content.index', 'admin.content.store', 'admin.content.update', 'admin.content.destroy', 'admin.events.index', 'admin.events.store', 'admin.events.update', 'admin.events.destroy',
            'admin.life-groups.index', 'admin.life-groups.store', 'admin.life-groups.update', 'admin.life-groups.destroy', 'admin.courses.index', 'admin.courses.store', 'admin.courses.update', 'admin.courses.destroy',
            'admin.media.index', 'admin.media.store', 'admin.media.update', 'admin.media.destroy', 'admin.church-locations.index', 'admin.church-locations.store', 'admin.church-locations.update', 'admin.church-locations.destroy',
            'admin.donations.index', 'admin.reports.index', 'admin.moderation.index', 'admin.prayer.index', 'admin.settings.index', 'admin.settings.update',
        ];
    }
}
