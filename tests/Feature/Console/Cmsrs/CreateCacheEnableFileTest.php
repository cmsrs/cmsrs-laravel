<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Cmsrs;

use App\Services\Cmsrs\ConfigService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateCacheEnableFileTest extends TestCase
{
    use RefreshDatabase;

    public function test_cache_enable_file_is_created(): void
    {
        $cacheFilePath = (new ConfigService)->getCacheFilePath();

        // Usuwamy plik przed testem, jeżeli już istnieje.
        if (file_exists($cacheFilePath)) {
            unlink($cacheFilePath);
        }

        $this->artisan('cmsrs:create-cache-enable-file')
            ->assertExitCode(0);

        $this->assertFileExists($cacheFilePath);
    }

    public function test_existing_cache_enable_file_is_not_deleted(): void
    {
        $cacheFilePath = (new ConfigService)->getCacheFilePath();

        if (! file_exists($cacheFilePath)) {
            touch($cacheFilePath);
        }

        $this->assertFileExists($cacheFilePath);

        $this->artisan('cmsrs:create-cache-enable-file')
            ->assertExitCode(0);

        $this->assertFileExists($cacheFilePath);
    }
}
