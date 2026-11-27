<?php

namespace Tests;

use App\Models\OrganisationUnit;
use App\Support\ChurchOfUgandaDioceses;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Id of an active diocese. The migrations seed them, but some suites (the
     * MySQL integration tests) wipe organisation_units, so re-seed if needed.
     */
    protected function dioceseId(string $name = 'Namirembe Diocese'): int
    {
        $find = fn () => OrganisationUnit::query()->where('type', 'diocese')->where('name', $name)->value('id');

        if ($find() === null) {
            ChurchOfUgandaDioceses::seed();
        }

        return (int) $find();
    }
}
