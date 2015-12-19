<?php

namespace App\Console\Commands;

use DB;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncMentors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Sync:Mentors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronises the current mentors with the forums.';

    protected $rtsIDs = [];
    protected $atcCutoffDate;
    protected $pilotCutoffDate;
    protected $memberForumIDs;
    protected $forumGroupIDs = [];

    /**
     * Create a new command instance.
     */
    public function initialise()
    {
        $this->atcCutoffDate = Carbon::now()->subMonths(6);
        $this->pilotCutoffDate = Carbon::now()->subYears(5);
        require_once('/var/www/community/init.php');
        require_once(\IPS\ROOT_PATH . '/system/Member/Member.php');
        require_once(\IPS\ROOT_PATH . '/system/Db/Db.php');
        foreach (DB::table('prod_rts.rts')->get(['id', 'name']) as $rts) {
            $this->rtsIDs[snake_case($rts->name)] = $rts->id;
        }
        $this->memberForumIDs = DB::table('prod_community.ibf_core_members')->lists('member_id', 'vatsim_cid');
        $this->forumGroupIDs = DB::table('prod_community.ibf_core_groups AS g')
            ->join('prod_community.ibf_core_sys_lang_words AS w', DB::raw('CONCAT("core_group_", g.g_id)'), '=', 'w.word_key')
            ->lists('word_default', 'g_id');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->initialise();
        $this->log("Initialised\n");

        $positions = DB::table('prod_rts.position_validations AS v')
            ->select(
                'v.member_id AS id',
                'v.position_id AS position',
                'p.callsign',
                'm.name',
                'rts.id as rts_id'
            )->leftJoin('prod_rts.positions AS p', 'p.id', '=', 'v.position_id')
            ->leftJoin('prod_rts.members AS m', 'v.member_id', '=', 'm.id')
            ->leftJoin('prod_rts.rts', 'p.rts_id', '=', 'rts.id')
            ->where('v.status', 5)
            ->orderBy('name')
            ->orderBy('callsign')
            ->get();

        $currentMentor = null;
        $currentForumMember = null;
        for ($i = 0; $i < count($positions); $i++) {
            // if we've started processing a new mentor
            if (!array_key_exists($i-1, $positions) || $positions[$i]->id !== $positions[$i-1]->id) {
                $this->log("Processing {$positions[$i]->id} - {$positions[$i]->name}\n");
                $currentMentor = $this->resetMentor($positions[$i]);
                $currentForumMember = \IPS\Member::load($this->memberForumIDs[$currentMentor->id]);
            }

            if (Carbon::parse($currentForumMember->joined)->gt(Carbon::now()->subDay())) {
                $this->log("Forum member not found.\n");
                continue;
            }

            // process the current row
            $groupSet = $this->determineGroup($currentMentor, $positions[$i], $currentForumMember);
            $currentMentor = $groupSet[0];
            $currentForumMember = $groupSet[1];

            // if this is the last row for the current mentor
            if (!array_key_exists($i+1, $positions) || $positions[$i]->id != $positions[$i+1]->id) {
                //$currentForumMember->save();
                $this->log("Finished processing {$currentMentor->id}\n");
                $this->log("========================================");
            }
        }
    }

    protected function resetMentor($mentor) {
        $mentor->level = [
            'P'     => false,
            'P1'    => false,
            'P2'    => false,
            'P3'    => false,
            'P4'    => false,
            'P5'    => false,
            'P6'    => false,
            'P7'    => false,
            'P8'    => false,
            'P9'    => false,
            'OBS'   => false,
            'TWR'   => false,
            'APP'   => false,
            'CTR'   => false,
            'MIL'   => false,
            'SHAN'  => false,
        ];
        $mentor->cutoff = [
            'atc'   => false,
            'pilot' => false,
        ];
        $mentor->atcMentor = false;
        $mentor->pilotMentor = false;

        // find the last ATC session time they accepted
        $lastSessionSQL = DB::table('prod_rts.sessions')
            ->where('taken', 1)
            ->where('mentor_id', $mentor->id)
            ->whereNull('cancelled_datetime')
            ->where('noShow', 0);

        $lastSession = $lastSessionSQL
            ->where('rts_id', '!=', $this->rtsIDs['pilots'])
            ->where('taken_date', '>', $this->atcCutoffDate)
            ->get(['id']);
        if (count($lastSession) < 1) {
            $mentor->cutoff['atc'] = true;
            $this->log("ATC cutoff reached.\n");
        }

        $lastSession = $lastSessionSQL
            ->where('rts_id', $this->rtsIDs['pilots'])
            ->where('taken_date', '>', $this->pilotCutoffDate)
            ->get(['id']);
        if (count($lastSession) < 1) {
            $mentor->cutoff['pilot'] = true;
            $this->log("Pilot cutoff reached.\n");
        }

        return $mentor;
    }

    protected function determineGroup($mentor, $position, $forumMember) {
        $addGroup = null;
        if ($position->rts_id === $this->rtsIDs['military']) {
            $mentor->level['MIL'] = true;
            $mentor->atcMentor = true;
            $addGroup = array_search('Secondary Only - ATC Mentor MIL', $this->forumGroupIDs);
        } else if (preg_match('/\_TWR$|\_GND$|\_SBTT$|\_SBGT$/i', $position->callsign)) {
            $mentor->level['TWR'] = true;
            $mentor->atcMentor = true;
            $addGroup = array_search('Secondary Only - ATC Mentor GNTW', $this->forumGroupIDs);
        } else if (preg_match('/\_APP$|\_SBAT$/i', $position->callsign)) {
            $mentor->level['APP'] = true;
            $mentor->atcMentor = true;
            $addGroup = array_search('Secondary Only - ATC Mentor APP', $this->forumGroupIDs);
        } else if (preg_match('/\_OBS$/i', $position->callsign)) {
            $mentor->level['OBS'] = true;
            $mentor->atcMentor = true;
            $addGroup = array_search('Secondary Only - ATC Mentor OBS', $this->forumGroupIDs);
        } else if (preg_match('/\_CTR$|\_SBCT$|^EGGX/i', $position->callsign)) {
            $mentor->level['CTR'] = true;
            $mentor->atcMentor = true;
            $addGroup = array_search('Secondary Only - ATC Mentor CTR', $this->forumGroupIDs);
        } else if ($position->rts_id === $this->rtsIDs['pilots']) {
            $mentor->level['P'] = true;
            $mentor->pilotMentor = true;
            switch (substr($position->callsign, 0, 2)) {
                case 'P1':
                    $mentor->level['P1'] = true;
                    $addGroup = array_search('Secondary Only - Pilot Mentor P1', $this->forumGroupIDs);
                    break;
                case 'P2':
                    $mentor->level['P2'] = true;
                    $addGroup = array_search('Secondary Only - Pilot Mentor P2', $this->forumGroupIDs);
                    break;
                case 'P3':
                    $mentor->level['P3'] = true;
                    $addGroup = array_search('Secondary Only - Pilot Mentor P3', $this->forumGroupIDs);
                    break;
                case 'P4':
                    $mentor->level['P4'] = true;
                    $addGroup = array_search('Secondary Only - Pilot Mentor P4', $this->forumGroupIDs);
                    break;
                case 'P5':
                    $mentor->level['P5'] = true;
                    $addGroup = array_search('Secondary Only - Pilot Mentor P5', $this->forumGroupIDs);
                    break;
                case 'P6':
                    $mentor->level['P6'] = true;
                    $addGroup = array_search('Secondary Only - Pilot Mentor P6', $this->forumGroupIDs);
                    break;
                case 'P7':
                    $mentor->level['P7'] = true;
                    $addGroup = array_search('Secondary Only - Pilot Mentor P7', $this->forumGroupIDs);
                    break;
                case 'P8':
                    $mentor->level['P8'] = true;
                    $addGroup = array_search('Secondary Only - Pilot Mentor P8', $this->forumGroupIDs);
                    break;
                case 'P9':
                    $mentor->level['P9'] = true;
                    $addGroup = array_search('Secondary Only - Pilot Mentor P9', $this->forumGroupIDs);
                    break;
                default:
                    // error determining pilot group - log it
            }
        } else {
            // error determining group - log it
        }

        $groups = $forumMember->groups;
        $pilotGroupID = array_search('Pilot Mentors', $this->forumGroupIDs);
        $atcGroupID = array_search('ATC Mentors', $this->forumGroupIDs);
        if (
            $addGroup && array_search($addGroup, $forumMember->groups) === false
            && (($position->rts_id === $this->rtsIDs['pilots']
                && !$mentor->cutoff['pilot'])
            || ($position->rts_id !== $this->rtsIDs['pilots']
                && !$mentor->cutoff['atc']))
        ) {
            $this->log("Adding group: {$addGroup}\n");
            array_push($groups, $addGroup);

        }

        if ($position->rts_id === $this->rtsIDs['pilots'] && array_search($pilotGroupID, $forumMember->groups) === false && !$mentor->cutoff['pilot']) {
            array_push($groups, $pilotGroupID);
            $this->log("Adding to pilot mentors group.\n");
        }

        if ($position->rts_id !== $this->rtsIDs['pilots'] && array_search($atcGroupID, $forumMember->groups) === false && !$mentor->cutoff['atc']) {
            array_push($groups, $atcGroupID);
            $this->log("Adding to ATC mentors group.\n");
        }

        $forumMember->set_mgroup_others(implode(',', $groups));

        return [$mentor, $forumMember];
    }

    protected function log($message)
    {
        if ($this->option('verbose')) {
            $this->output->write($message);
        }
    }
}
