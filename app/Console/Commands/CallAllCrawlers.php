<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CallAllCrawlers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'call-all-crawlers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calls all crawlers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->call('crawl-kommunity');
        $this->call('crawl-bugece');
    }
}
