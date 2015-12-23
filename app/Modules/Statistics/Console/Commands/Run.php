<?php

namespace App\Modules\Statistics\Console\Commands;

use App\Modules\Statistics\Jobs\StatisticsDownloadAndParse;
use DB;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use App\Models\Mship\Account;
use Illuminate\Foundation\Bus\DispatchesJobs;

class Run extends \App\Console\Commands\aCommand
{
    use DispatchesJobs;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'statistics:download';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the statistics jobs to download & prase the network data.';

    protected $sso_account_id;

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
        $downloadJob = (new StatisticsDownloadAndParse())->onQueue("low");
        $this->dispatch($downloadJob);
    }
}