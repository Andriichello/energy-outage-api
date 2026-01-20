<?php

namespace App\Console\Commands;

use App\Jobs\FetchUpdatedInformationForZakarpattia;
use Illuminate\Console\Command;

class FetchUpdatedInformationForAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-updated-information-for-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch updated information for all energy providers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dispatch(new FetchUpdatedInformationForZakarpattia());
    }
}
