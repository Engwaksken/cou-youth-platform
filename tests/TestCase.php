<?php

namespace Tests;

use App\Models\OrganisationUnit;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** Id of an active diocese (the migrations seed the Church of Uganda dioceses). */
    protected function dioceseId(string $name = 'Namirembe Diocese'): int
    {
        return (int) OrganisationUnit::query()
            ->where('type', 'diocese')
            ->where('name', $name)
            ->value('id');
    }
}
