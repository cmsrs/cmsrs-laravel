<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Cmsrs;

use App\Models\Cmsrs\Cms\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoadWelcomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_load_welcome_page_command_runs_successfully(): void
    {
        $this->artisan('cmsrs:load-welcome-page')
            ->assertExitCode(0);
    }

    public function test_welcome_page_is_created(): void
    {
        $this->artisan('cmsrs:load-welcome-page')
            ->assertExitCode(0);

        $this->assertDatabaseHas('pages', [
            'type' => 'main_page',
            'published' => 1,
            'commented' => 0,
            'after_login' => 0,
        ]);
    }

    public function test_welcome_page_is_not_duplicated(): void
    {
        $this->artisan('cmsrs:load-welcome-page')
            ->assertExitCode(0);

        $firstCount = Page::where('type', 'main_page')->count();

        $this->artisan('cmsrs:load-welcome-page')
            ->assertExitCode(0);

        $secondCount = Page::where('type', 'main_page')->count();

        $this->assertEquals(
            $firstCount,
            $secondCount
        );
    }
}
