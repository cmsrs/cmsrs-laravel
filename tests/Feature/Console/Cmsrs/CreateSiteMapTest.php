<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Cmsrs;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateSiteMapTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_site_map_command_runs_successfully(): void
    {
        $siteMapPath = public_path('sitemap.txt');

        if (file_exists($siteMapPath)) {
            unlink($siteMapPath);
        }

        $this->artisan('cmsrs:create-site-map')
            ->assertExitCode(0);

        $this->assertFileExists($siteMapPath);
    }

    public function test_sitemap_file_is_not_empty(): void
    {
        $siteMapPath = public_path('sitemap.txt');

        $this->artisan('cmsrs:create-site-map')
            ->assertExitCode(0);

        $this->assertFileExists($siteMapPath);

        $content = file_get_contents($siteMapPath);

        $this->assertNotFalse($content);
        $this->assertNotEmpty($content);
    }
}
