<?php

declare(strict_types=1);

namespace App\Console\Commands\Cmsrs;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cmsrs:send-test-email {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send test email';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $toEmail = $this->argument('email');

        if (! $toEmail) {
            echo 'put email receiver';
            exit;
        }

        Mail::raw('this is test msg body', function ($message) use ($toEmail) {
            $message->to($toEmail)
                ->subject('this is test title');
        });

        // $toName = 'J K';
        // $data = array('name'=>"Jan Kowalski", "body" => "Test mail");
        // Mail::send('emails', $data, function($message) use ($toName, $toEmail) {
        //     $message->to($toEmail, $toName)->subject('Artisans Web Testing Mail');
        //     $message->from('test@gmail.com','Artisans Web');
        // });
        return 0;
    }
}
