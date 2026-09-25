<?php

namespace Tests\Unit;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteBrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_setting_get_returns_default_when_missing(): void
    {
        $this->assertSame('Church of Uganda Youth Platform', SiteSetting::get('system_name', 'Church of Uganda Youth Platform'));
        $this->assertNull(SiteSetting::get('logo'));
    }

    public function test_site_setting_set_and_get_round_trip(): void
    {
        SiteSetting::set('system_name', 'COU Youth');
        $this->assertSame('COU Youth', SiteSetting::get('system_name'));
    }
}
