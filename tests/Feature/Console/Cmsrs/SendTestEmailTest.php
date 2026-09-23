<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Cmsrs;

use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendTestEmailTest extends TestCase
{
    public function test_test_email_is_sent(): void
    {
        Mail::fake();

        $this->artisan('cmsrs:send-test-email', [
            'email' => 'test@example.com',
        ])
            ->assertExitCode(0);

        // Mail::assertSent(function ($mail) {
        //     return $mail->to[0]['address'] === 'test@example.com'
        //         && $mail->subject === 'this is test title';
        // });
    }
}
