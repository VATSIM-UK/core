<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Models\Mship\Account\Account;
use Models\Mship\Account\Email;
use Models\Mship\Account\State;
use Models\Mship\Qualification as QualificationData;
use Models\Mship\Account\Qualification;
use \Cache;
use \VatsimSSO;
use \Enums\Account\State as EnumState;

class CommunitySync extends aCommand {

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
    protected $description = 'Core member database import for Community.';

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
        define('IN_ACP', TRUE);
        define('IPS_ENFORCE_ACCESS', TRUE);
        define('IPB_THIS_SCRIPT', 'private');
        require_once('/var/www/community/initdata.php');
        require_once(IPS_ROOT_PATH . 'sources/base/ipsRegistry.php');
        require_once(IPS_ROOT_PATH . 'sources/base/ipsController.php');

        ipsRegistry::init();
        ipsRegistry::DB()->build(
                    array(
                        'select' => 'member_id, name, email, members_display_name, title',
                        'from' => 'members'
                    ));
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
            echo $_member["member_id"] . " // " . $_member["name"] . ":";
            
            if (!is_numeric($_member["name"])) {
                $countFailure++;
                echo "\tError: name is not a CID - member ID " . $_member["member_id"] . "\n";
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
            
            // State!
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
            
            // Check for changes!
            $changeEmail = strcasecmp($_member["email"], $email);
            $changeName = strcmp($_member["members_display_name"], $member->name_first . " " . $member->name_last);
            $changeState = strcasecmp($_member["title"], $state);
            
            // Confirm the data!
            echo "\tID: " . $member->account_id;
            echo "\tEmail (" . ($emailLocal ? "local" : "latest") . "):" . $email . ($changeEmail ? "(changed)" : "");
            echo "\tDisplay: " . $member->name_first . " " . $member->name_last . ($changeName ? "(changed)" : "");
            echo "\tState: " . $state . ($changeState ? "(changed)" : "");
            echo "\tATC rating: " . $aRatingString;
            echo "\tPilot ratings: " . $pRatingString;
            echo "\tExtra ratings: " . $eRatingString;
            
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
                echo "\tSaved details.";
            } catch (Exception $e) {
                $countFailure++;
                echo "\tError saving details to forum.";
            }
            echo "\n";
        }

        echo "Run Results:";
        echo "Total Accounts: ".$countTotal;
        echo "Successful Updates: ".$countSuccess;
        echo "Failed Updates: ".$countFailure;
    }
}
