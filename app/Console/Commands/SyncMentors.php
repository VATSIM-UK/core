<?php

namespace App\Console\Commands;

use DB;
use Carbon\Carbon;

/**
 * Synchronises mentors in the RTS system to the forums.
 *
 * This class is mostly a port from the old sync script, and
 * therefore requires further improvement.
 * Does not include the 'new pilots' RTS/positions.
 *
 * @todo Add more detailed logging
 * @todo Remove groups from ineligible members
 */
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
    protected $pilotGroupID;
    protected $atcGroupID;

    /**
     * Create a new command instance.
     */
    public function initialise()
    {
        // set the cutoff date
        $this->atcCutoffDate = Carbon::now()->subMonths(6);
        $this->pilotCutoffDate = Carbon::now()->subYears(5);

        // intialise scripts for interfacing with the forums
        require_once '/var/www/community/init.php';
        require_once \IPS\ROOT_PATH.'/system/Member/Member.php';
        require_once \IPS\ROOT_PATH.'/system/Db/Db.php';

        // get the relevant DB IDs
        foreach (DB::table('prod_rts.rts')->get(['id', 'name']) as $rts) {
            $this->rtsIDs[snake_case($rts->name)] = $rts->id;
        }
        $this->memberForumIDs = DB::table('prod_community.ibf_core_members')->pluck('member_id', 'vatsim_cid');
        $this->forumGroupIDs = DB::table('prod_community.ibf_core_groups AS g')
            ->join('prod_community.ibf_core_sys_lang_words AS w', DB::raw('CONCAT("core_group_", g.g_id)'), '=', 'w.word_key')
            ->pluck('word_default', 'g_id');
        $this->pilotGroupID = array_search('Pilot Mentors', $this->forumGroupIDs);
        $this->atcGroupID = array_search('ATC Mentors', $this->forumGroupIDs);

        $this->log('Command initialised.');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->initialise();
        $this->addGroupsToMembers();
    }

    protected function addGroupsToMembers()
    {
        // get all mentor position assignments
        $positions = DB::table('prod_rts.position_validations AS v')
            ->select(
                'v.member_id AS id',
                'v.position_id AS position',
                'p.callsign',
                'm.name',
                'rts.id as rts_id',
                DB::raw('IF (v.member_id IN (SELECT DISTINCT mentor_id FROM prod_rts.sessions WHERE taken = 1 AND cancelled_datetime IS NULL AND noShow = 0 AND rts_id != '.$this->rtsIDs['pilots'].' AND taken_date > "'.$this->atcCutoffDate.'"), FALSE, TRUE) AS atc_cutoff'),
                DB::raw('IF (v.member_id IN (SELECT DISTINCT mentor_id FROM prod_rts.sessions WHERE taken = 1 AND cancelled_datetime IS NULL AND noShow = 0 AND rts_id = '.$this->rtsIDs['pilots'].' AND taken_date > "'.$this->pilotCutoffDate.'"), FALSE, TRUE) AS pilot_cutoff')
            )->leftJoin('prod_rts.positions AS p', 'p.id', '=', 'v.position_id')
            ->leftJoin('prod_rts.members AS m', 'v.member_id', '=', 'm.id')
            ->leftJoin('prod_rts.rts', 'p.rts_id', '=', 'rts.id')
            ->where('v.status', 5)
            ->orderBy('name')
            ->orderBy('callsign')
            ->having('atc_cutoff', '=', 0)
            ->orHaving('pilot_cutoff', '=', 0)
            ->get();

        $currentMentor = null;
        $currentForumMember = null;
        for ($i = 0; $i < count($positions); $i++) {
            // if we've started processing a new mentor
            if (!array_key_exists($i - 1, $positions) || $positions[$i]->id !== $positions[$i - 1]->id) {
                $this->log("Processing {$positions[$i]->id} - {$positions[$i]->name}");
                $currentMentor = $positions[$i];
                $currentForumMember = \IPS\Member::load($this->memberForumIDs[$currentMentor->id]);
                $this->log("ATC cutoff: {$currentMentor->atc_cutoff}");
                $this->log("Pilot cutoff: {$currentMentor->pilot_cutoff}");
            }

            // the forum classes suck
            if (Carbon::parse($currentForumMember->joined)->gt(Carbon::now()->subDay())) {
                $this->log('Forum member not found.');
                continue;
            }

            // process the current row
            $currentForumMember = $this->determineGroup($positions[$i], $currentForumMember);

            // if this is the last row for the current mentor
            if (!array_key_exists($i + 1, $positions) || $positions[$i]->id != $positions[$i + 1]->id) {
                $groups = explode(',', $currentForumMember->mgroup_others);
                $groups = implode("\n", array_map(function ($s) {
                    return $this->forumGroupIDs[$s];
                }, $groups));
                $this->log("Current groups:\n{$groups}");
                $this->log("Finished processing {$currentMentor->id}");
                $this->log('========================================');
                $currentForumMember->save();
            }
        }
    }

    protected function determineGroup($position, $forumMember)
    {
        // don't process if the cutoff has been reached
        if (($position->rts_id === $this->rtsIDs['pilots'] && $position->pilot_cutoff)
            || ($position->rts_id !== $this->rtsIDs['pilots'] && $position->atc_cutoff)
        ) {
            $this->log("Cutoff reached - skipping position {$position->position}");

            return $forumMember;
        }

        // add the group
        $addGroup = $this->calculateGroupID($position);
        $groups = explode(',', $forumMember->mgroup_others);
        if ($addGroup && array_search($addGroup, $groups) === false) {
            $this->log("Adding group: {$addGroup}");
            array_push($groups, $addGroup);
        }

        // add them to the overall atc/pilot mentoring groups
        if ($position->rts_id === $this->rtsIDs['pilots'] && array_search($this->pilotGroupID, $groups) === false) {
            array_push($groups, $this->pilotGroupID);
            $this->log('Adding to pilot mentors group.');
        }

        if ($position->rts_id !== $this->rtsIDs['pilots'] && array_search($this->atcGroupID, $groups) === false) {
            array_push($groups, $this->atcGroupID);
            $this->log('Adding to ATC mentors group.');
        }

        $forumMember->mgroup_others = implode(',', $groups);

        return $forumMember;
    }

    protected function calculateGroupID($position)
    {
        $addGroup = null;
        if ($position->rts_id === $this->rtsIDs['military']) {
            $addGroup = array_search('Secondary Only - ATC Mentor MIL', $this->forumGroupIDs);
        } elseif (preg_match('/\_TWR$|\_GND$|\_DEL$|\_SBTT$|\_SBGT$/i', $position->callsign)) {
            $addGroup = array_search('Secondary Only - ATC Mentor GNTW', $this->forumGroupIDs);
        } elseif (preg_match('/\_APP$|\_SBAT$/i', $position->callsign)) {
            $addGroup = array_search('Secondary Only - ATC Mentor APP', $this->forumGroupIDs);
        } elseif (preg_match('/\_OBS$/i', $position->callsign)) {
            $addGroup = array_search('Secondary Only - ATC Mentor OBS', $this->forumGroupIDs);
        } elseif (preg_match('/\_CTR$|\_SBCT$|^EGGX/i', $position->callsign)) {
            $addGroup = array_search('Secondary Only - ATC Mentor CTR', $this->forumGroupIDs);
        } elseif ($position->rts_id === $this->rtsIDs['pilots']) {
            switch (substr($position->callsign, 0, 2)) {
                case 'P1':
                    $addGroup = array_search('Secondary Only - Pilot Mentor P1', $this->forumGroupIDs);
                    break;
                case 'P2':
                    $addGroup = array_search('Secondary Only - Pilot Mentor P2', $this->forumGroupIDs);
                    break;
                case 'P3':
                    $addGroup = array_search('Secondary Only - Pilot Mentor P3', $this->forumGroupIDs);
                    break;
                case 'P4':
                    $addGroup = array_search('Secondary Only - Pilot Mentor P4', $this->forumGroupIDs);
                    break;
                case 'P5':
                    $addGroup = array_search('Secondary Only - Pilot Mentor P5', $this->forumGroupIDs);
                    break;
                case 'P6':
                    $addGroup = array_search('Secondary Only - Pilot Mentor P6', $this->forumGroupIDs);
                    break;
                case 'P7':
                    $addGroup = array_search('Secondary Only - Pilot Mentor P7', $this->forumGroupIDs);
                    break;
                case 'P8':
                    $addGroup = array_search('Secondary Only - Pilot Mentor P8', $this->forumGroupIDs);
                    break;
                case 'P9':
                    $addGroup = array_search('Secondary Only - Pilot Mentor P9', $this->forumGroupIDs);
                    break;
                default:
                    $this->log("Error determining pilot group - {$position->position}.");
            }
        } else {
            $this->log("Error determining position group - {$position->position}.");
        }

        return $addGroup;
    }
}
