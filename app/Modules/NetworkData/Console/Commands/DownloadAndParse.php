<?php

namespace App\Modules\NetworkData\Console\Commands;

use App\Modules\NetworkData\Jobs\NetworkDataDownloadAndParse as StatisticsDownloadAndParseJob;
use Illuminate\Foundation\Bus\DispatchesJobs;

class DownloadAndParse extends \App\Console\Commands\Command
{
    use DispatchesJobs;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'networkdata:download';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download and parse the VATSIM data feed file.';

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
     * Executes all necessary console commands.
     *
     * @return mixed
     */
    public function fire()
    {
        $downloadJob = (new StatisticsDownloadAndParseJob())->onQueue('low');
        $this->dispatch($downloadJob);
    }
}
