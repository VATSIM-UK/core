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

    public function getIndex($sort_by="account_id", $sort_dir="ASC", $page=1) {
        $totalMembers = AccountData::count();
        $memberSearch = new AccountData;

        // Pagination!
        $limit = 50;
        $minPage = 1;
        $maxPage = ceil($totalMembers/$limit);
        $page = ($page > $maxPage) ? $maxPage : $page;
        $page = ($page < 1 ? 1 : $page);
        $offset = ($page-1)*$limit;

        // Sorting and searching!
        $sortBy = in_array($sort_by, ["account_id", "name_first", "name_last"]) ? $sort_by : "account_id";
        $sortDir = in_array($sort_dir, ["ASC", "DESC"]) ? $sort_dir : "ASC";

        // ORM it all!
        $memberSearch = $memberSearch->orderBy($sortBy, $sortDir)
                                     ->offset($offset)
                                     ->limit($limit);

        return $this->viewMake("adm.mship.account.index")
                    ->with("members", $memberSearch->get())
                    ->with("sortBy", $sortBy)
                    ->with("sortDir", $sortDir)
                    ->with("sortDirSwitch", ($sortDir == "DESC" ? "ASC" : "DESC"))
                    ->with("pageCur", $page)
                    ->with("pageNext", ($page+1 < $maxPage ? $page+1 : null))
                    ->with("pagePrev", ($page-1 > 1 ? $page-1 : null))
                    ->with("paginationStart", ($page-2 > 0 ? $page-2 : 1));
    }

    public function getDetail(AccountData $account, $tab="basic") {
        if (!$account) {
            return Redirect::route("adm.account.index");
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
            return Redirect::route("adm.account.index");
        }

        // Let's check the user doesn't currently have security on their account.
        // We don't want to just override it for no reason, as that's bad.
        $currentSecurity = $account->current_security;

        if($currentSecurity && $currentSecurity->exists){
            return Redirect::route("adm.account.details", [$account->account_id, "security"])->withError("You cannot enable security on this account.");
        }

        // Check the selected security ID exists!
        $security = SecurityData::find(Input::get("securityLevel", 0));

        if(!$security){
            return Redirect::route("adm.account.details", [$account->account_id, "security"])->withError("Invalid security ID specified.");
        }

        // Create them a blank security entry!
        $newSecurity = new AccountSecurityData();
        $newSecurity->save();
        $account->security()->save($newSecurity);
        $security->accountSecurity()->save($newSecurity);

        return Redirect::route("adm.account.details", [$account->account_id, "security"])->withSuccess("Security enabled for this account.");
    }

    public function postSecurityReset(AccountData $account){
        if (!$account) {
            return Redirect::route("adm.account.index");
        }

        // Let's check the user doesn't currently have security on their account.
        // We can't reset non-existant security!
        $currentSecurity = $account->current_security;

        if(!$currentSecurity OR !$currentSecurity->exists){
            return Redirect::route("adm.account.details", [$account->account_id, "security"])->withError("You cannot reset non-existant security.");
        }

        $account->resetPassword(true);

        return Redirect::route("adm.account.details", [$account->account_id, "security"])->withSuccess("Security reset requested - user will receive an email.");
    }

    public function postSecurityChange(AccountData $account){
        if (!$account) {
            return Redirect::route("adm.account.index");
        }

        // Check the selected security ID exists!
        $security = SecurityData::find(Input::get("securityLevel", 0));

        if(!$security){
            return Redirect::route("adm.account.details", [$account->account_id, "security"])->withError("Invalid security ID specified.");
        }

        // Let's check the user doesn't currently have security on their account.
        // We don't want to just override it for no reason, as that's bad.
        $currentSecurity = $account->current_security;

        // It's also pointless changing to the same security ID.
        if(!$currentSecurity OR !$currentSecurity->exists OR $currentSecurity->security_id == $security->security_id){
            return Redirect::route("adm.account.details", [$account->account_id, "security"])->withError("You cannot change security on this account.");
        }

        // Let's expire the current security
        $currentSecurity->expire();
        $currentSecurity->delete();

        // Now, let's make a new one!
        $newSecurity = new AccountSecurityData();
        $newSecurity->save();
        $account->security()->save($newSecurity);
        $security->accountSecurity()->save($newSecurity);

        return Redirect::route("adm.account.details", [$account->account_id, "security"])->withSuccess("Security has been upgraded on this account.");
    }
}
