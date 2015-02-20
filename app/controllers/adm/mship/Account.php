<?php

namespace Controllers\Adm\Mship;

use \AuthException;
use \Input;
use \Session;
use \Response;
use \Request;
use \View;
use \VatsimSSO;
use \Config;
use \Redirect;
use \DB;
use \Models\Mship\Account as AccountData;
use \Models\Mship\Account\Security as AccountSecurityData;
use Models\Mship\Security as SecurityData;

class Account extends \Controllers\Adm\AdmController {

    public function getIndex($sort_by="account_id", $sort_dir="ASC") {
        $totalMembers = AccountData::count();
        $memberSearch = new AccountData;

        // Sorting and searching!
        $sortBy = in_array(Input::get("sort_by", $sort_by), ["account_id", "name_first", "name_last"]) ? Input::get("sort_by", $sort_by) : "account_id";
        $sortDir = in_array(Input::get("sort_dir", $sort_dir), ["ASC", "DESC"]) ? Input::get("sort_dir", $sort_dir) : "ASC";

        // ORM it all!
        $memberSearch = AccountData::orderBy($sortBy, $sortDir)
                                   ->paginate(50);

        return $this->viewMake("adm.mship.account.index")
                    ->with("members", $memberSearch)
                    ->with("sortBy", $sortBy)
                    ->with("sortDir", $sortDir)
                    ->with("sortDirSwitch", ($sortDir == "DESC" ? "ASC" : "DESC"));
    }

    public function getDetail(AccountData $account, $tab="basic") {
        if (!$account) {
            return Redirect::route("adm.mship.account.index");
        }

        // Get all possible security levels.
        $securityLevels = SecurityData::all();

        $this->_pageTitle = "Account Details: " . $account->name;

        return $this->viewMake("adm.mship.account.detail")
                        ->with("selectedTab", $tab)
                        ->with("account", $account)
                        ->with("securityLevels", $securityLevels);
    }

    public function postSecurityEnable(AccountData $account){
        if (!$account) {
            return Redirect::route("adm.mship.account.index");
        }

        // Let's check the user doesn't currently have security on their account.
        // We don't want to just override it for no reason, as that's bad.
        $currentSecurity = $account->current_security;

        if($currentSecurity && $currentSecurity->exists){
            return Redirect::route("adm.mship.account.details", [$account->account_id, "security"])->withError("You cannot enable security on this account.");
        }

        // Check the selected security ID exists!
        $security = SecurityData::find(Input::get("securityLevel", 0));

        if(!$security){
            return Redirect::route("adm.mship.account.details", [$account->account_id, "security"])->withError("Invalid security ID specified.");
        }

        // Create them a blank security entry!
        $newSecurity = new AccountSecurityData();
        $newSecurity->save();
        $account->security()->save($newSecurity);
        $security->accountSecurity()->save($newSecurity);

        return Redirect::route("adm.mship.account.details", [$account->account_id, "security"])->withSuccess("Security enabled for this account.");
    }

    public function postSecurityReset(AccountData $account){
        if (!$account) {
            return Redirect::route("adm.mship.account.index");
        }

        // Let's check the user doesn't currently have security on their account.
        // We can't reset non-existant security!
        $currentSecurity = $account->current_security;

        if(!$currentSecurity OR !$currentSecurity->exists){
            return Redirect::route("adm.mship.account.details", [$account->account_id, "security"])->withError("You cannot reset non-existant security.");
        }

        $account->resetPassword(true);

        return Redirect::route("adm.mship.account.details", [$account->account_id, "security"])->withSuccess("Security reset requested - user will receive an email.");
    }

    public function postSecurityChange(AccountData $account){
        if (!$account) {
            return Redirect::route("adm.mship.account.index");
        }

        // Check the selected security ID exists!
        $security = SecurityData::find(Input::get("securityLevel", 0));

        if(!$security){
            return Redirect::route("adm.mship.account.details", [$account->account_id, "security"])->withError("Invalid security ID specified.");
        }

        // Let's check the user doesn't currently have security on their account.
        // We don't want to just override it for no reason, as that's bad.
        $currentSecurity = $account->current_security;

        // It's also pointless changing to the same security ID.
        if(!$currentSecurity OR !$currentSecurity->exists OR $currentSecurity->security_id == $security->security_id){
            return Redirect::route("adm.mship.account.details", [$account->account_id, "security"])->withError("You cannot change security on this account.");
        }

        // Let's expire the current security
        $currentSecurity->expire();
        $currentSecurity->delete();

        // Now, let's make a new one!
        $newSecurity = new AccountSecurityData();
        $newSecurity->save();
        $account->security()->save($newSecurity);
        $security->accountSecurity()->save($newSecurity);

        return Redirect::route("adm.mship.account.details", [$account->account_id, "security"])->withSuccess("Security has been upgraded on this account.");
    }
}
