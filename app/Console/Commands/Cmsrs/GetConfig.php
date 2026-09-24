<?php

declare(strict_types=1);

namespace App\Console\Commands\Cmsrs;

use Illuminate\Console\Command;

class GetConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cmsrs:getconfig';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get config - use for testing perpose';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $admEmail = config('cmsrs.admin.email'); // env('ADM_EMAIL', 'adm@cmsrs.pl');
        $admPass = config('cmsrs.admin.password'); // env('ADM_PASS', 'cmsrs123');

        $cmdLogin = 'curl  -H "Accept:application/json" -H "Content-Type:application/json" -XPOST  "http://127.0.0.1:8000/api/login" -d \'{ 
            "email": "'.$admEmail.'", 
            "password": "'.$admPass.'"
        }\'';

        $out = [];
        exec($cmdLogin, $out);

        $res1 = json_decode($out[0]);

        if ($res1->success) {
            $token = $res1->data->token;
        } else {
            exit('something wrong with login');
        }

        $cmdGetConfig = "curl  -H 'Accept:application/json' -H 'Content-Type:application/json' -XGET  'http://127.0.0.1:8000/api/config?token=$token'";

        $out2 = [];
        exec($cmdGetConfig, $out2);

        print_r($out2);

        return 0;
    }
}
