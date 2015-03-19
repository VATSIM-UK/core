<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Models\Mship\Account;
use Enums\Account\State as EnumState;

class SyncCommunity extends aCommand {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'Sync:Community';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync membership data from Core to Community.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire() {
        // setup
        if ($this->option("debug")) $debug = TRUE;
        else $debug = FALSE;

        define('IN_ACP', TRUE);
        define('IPS_ENFORCE_ACCESS', TRUE);
        define('IPB_THIS_SCRIPT', 'private');
        require_once('/var/www/community/initdata.php');
        require_once(IPS_ROOT_PATH . 'sources/base/ipsRegistry.php');
        require_once(IPS_ROOT_PATH . 'sources/base/ipsController.php');

        $members_sql = array();
        $members_sql['select'] = 'm.member_id, m.name, m.email, m.members_display_name, m.title';
        $members_sql['from'] = ['members' => 'm'];
        if ($this->option("force-update"))
            $members_sql['where'] = "name = {$this->option('force-update')}";
        $members_sql['add_join'] = [
            [
            'select' => 'p.field_12, p.field_13, p.field_14, p.field_16',
            'from' => array('pfields_content' => 'p'),
            'where' => 'm.member_id = p.member_id',
            'type' => 'left'
            ]
        ];

        ipsRegistry::init();
        ipsRegistry::DB()->build($members_sql);
        $_members = ipsRegistry::DB()->execute();
        $countTotal = ipsRegistry::DB()->getTotalRows();
        $countSuccess = 0;
        $countFailure = 0;

        // loop through members
        while ($_member = ipsRegistry::DB()->fetch($_members)) {
            if (!$_member["member_id"] || !$_member["name"]) {
                $countFailure++;
                continue;
            }
            if ($debug) echo $_member["member_id"] . " // " . $_member["name"] . ":";

            if (!is_numeric($_member["name"])) {
                $countFailure++;
                if ($debug) echo "\tError: name is not a CID - member ID " . $_member["member_id"] . "\n";
                continue;
            }

            // retrieve member from core
            try {
                $member = Account::findOrFail($_member["name"]);
            } catch (Exception $e) {
                $countFailure++;
                echo "\tError: cannot retrieve member " . $_member["member_id"] . "from Core - " . $e->getMessage() . "\n";
                continue;
            }

            // Sort out their email
            $emailLocal = false;
            $email = $member->primary_email;
            if (empty($email)) {
                $email = $_member["email"];
                $emailLocal = true;
            }

            // State
            //$state = $member->getIsStateAttribute(EnumState::DIVISION)->first()->state ? "Division Member" : "International Member";
            //$state = $member->getIsStateAttribute(EnumState::VISITOR)->first()->state ? "Visiting Member" : $state;
            $state = $member->states()->where("state", "=", EnumState::DIVISION)->first()->state ? "Division Member" : "International Member";
            $state = $member->states()->where("state", "=", EnumState::VISITOR)->first()->state ? "Visiting Member" : $state;

            // ATC rating
            $aRatingString = $member->qualification_atc->qualification->name_long;

            // Sort out the pilot rating.
            $pRatingString = $member->qualifications_pilot_string;

            // Get extra and admin ratings
            $eRatingString = "";
            $eRatings = $member->qualifications_atc_training;
            foreach($eRatings as $eRating){
                $eRatingString .= $eRating->qualification->name_long . ", ";
            }
            $eRatings = $member->qualifications_pilot_training;
            foreach($eRatings as $eRating){
                $eRatingString .= $eRating->qualification->name_long . ", ";
            }
            $eRatings = $member->qualifications_admin;
            foreach($eRatings as $eRating){
                $eRatingString .= $eRating->qualification->name_long . ", ";
            }
            $eRatingString = trim($eRatingString, ", ");

            // Check for changes
            $changeEmail = strcasecmp($_member["email"], $email);
            $changeName = strcmp($_member["members_display_name"], $member->name_first . " " . $member->name_last);
            $changeState = strcasecmp($_member["title"], $state);
            $changeCID = strcmp($_member["field_12"], $member->account_id);
            $changeARating = strcmp($_member["field_13"], $aRatingString);
            $changePRating = strcmp($_member["field_14"], $pRatingString);
            $changeERating = strcmp($_member["field_16"], $eRatingString);
            $changesPending = $changeEmail || $changeName || $changeState || $changeCID
                              || $changeARating || $changePRating || $changeERating;

            // Confirm the data
            if ($debug) {
                echo "\tID: " . $member->account_id;
                echo "\tEmail (" . ($emailLocal ? "local" : "latest") . "):" . $email . ($changeEmail ? "(changed)" : "");
                echo "\tDisplay: " . $member->name_first . " " . $member->name_last . ($changeName ? "(changed)" : "");
                echo "\tState: " . $state . ($changeState ? "(changed)" : "");
                echo "\tATC rating: " . $aRatingString;
                echo "\tPilot ratings: " . $pRatingString;
                echo "\tExtra ratings: " . $eRatingString;
            }

            if ($changesPending) {
                try {
                    IPSMember::save($_member["member_id"],
                                    array( 'members' => array(
                                                'email'  => $email,
                                                'members_display_name' => $member->name_first . " " . $member->name_last,
                                                'title' => $state,
                                        ),
                                        'pfields_content' => array(
                                            'field_12' => $member->account_id, // VATSIM CID
                                            'field_13' => $aRatingString, // Controller Rating
                                            'field_14' => $pRatingString, // Pilot Ratings
                                            'field_16' => $eRatingString, // Extra Ratings
                                        )));
                    $countSuccess++;
                    if ($debug) echo "\tSaved details.";
                } catch (Exception $e) {
                    $countFailure++;
                    echo "\tError saving " . $member->account_id . "details to forum.";
                }
            } else {
                if ($debug) echo "\tNo changes required.";
            }
            if ($debug) echo "\n";
        }

        if ($debug) echo "Run Results:";
        if ($debug) echo "Total Accounts: ".$countTotal;
        if ($debug) echo "Successful Updates: ".$countSuccess;
        if ($debug) echo "Failed Updates: ".$countFailure;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions() {
        return array(
            array("force-update", "f", InputOption::VALUE_OPTIONAL, "If specified, only this CID will be checked.", 0),
            array("debug", "d", InputOption::VALUE_NONE, "Enable debug output."),
        );
    }
}
