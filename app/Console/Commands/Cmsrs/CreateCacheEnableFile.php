<?php

declare(strict_types=1);

namespace App\Console\Commands\Cmsrs;

use App\Services\Cmsrs\ConfigService;
use Illuminate\Console\Command;

class CreateCacheEnableFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cmsrs:create-cache-enable-file';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Cache Enable File';

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
        $cacheFilePath = (new ConfigService)->getCacheFilePath();
        if (! file_exists($cacheFilePath)) {
            touch($cacheFilePath);
        }

        return 0;
    }
}
