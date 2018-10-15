<?php

namespace App\Console\Commands\Community\Badges;

use Alawrence\Ipboard\Facades\Ipboard;
use App\Repositories\Cts\SessionRepository;
use Illuminate\Console\Command;

class Mentoring28 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'community:badges:mentoring28';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Award all mentors badges that have mentored in the last 28 days.';

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
     * @return mixed
     */
    public function handle()
    {
        $mentorIDs = resolve(SessionRepository::class)->mentorIdsForSessionsInLast28Days();

        // ToDo: Probably some checks to see who already has this badge?

        foreach ($mentorIDs as $mentorID) {
            Ipboard::awardBadge($mentorID, 1);
        }
    }
}
