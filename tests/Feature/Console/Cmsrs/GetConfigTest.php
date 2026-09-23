<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Cmsrs;

use Tests\TestCase;

class GetConfigTest extends TestCase
{
    public function test_get_config_command_runs_successfully(): void
    {
        $this->markTestSkipped('This test is skipped because it requires a specific environment setup.
        it is required to have a local server running');
        $this->artisan('cmsrs:getconfig')
            ->assertExitCode(0);
    }
}
