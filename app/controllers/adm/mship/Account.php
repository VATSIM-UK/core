<?php

namespace Controllers\Adm\Mship;

use Auth;
use Config;
use DB;
use Illuminate\Support\Collection;
use Input;
use Models\Mship\Account as AccountData;
use Models\Mship\Account\Note as AccountNoteData;
use Models\Mship\Account\Security as AccountSecurityData;
use Models\Mship\Note\Type as NoteTypeData;
use Models\Mship\Role as RoleData;
use Models\Mship\Security as SecurityData;
use Models\Sys\Timeline\Entry as TimelineEntryData;
use Redirect;
use Request;
use Response;
use Session;
use URL;
use VatsimSSO;
use View;

class Account extends \Controllers\Adm\AdmController {

    public function getIndex($scope = "division") {
        $totalMembers = AccountData::count();
        $memberSearch = new AccountData;

        // Sorting and searching!
        $sortBy = in_array(Input::get("sort_by"), ["account_id", "name_first", "name_last"]) ? Input::get("sort_by") : "account_id";
        $sortDir = in_array(Input::get("sort_dir"), ["ASC", "DESC"]) ? Input::get("sort_dir") : "ASC";

        // ORM it all!
        $memberSearch = AccountData::isNotSystem()
                                   ->orderBy($sortBy, $sortDir)
                                   ->with("qualifications")
                                   ->with("qualifications.qualification")
                                   ->with("states")
                                   ->with("emails");

        switch($scope) {
            case "active":
                $memberSearch = $memberSearch->where("status", "=", 0);
                break;
            case "inactive":
                $memberSearch = $memberSearch->where(DB::raw("status&" . AccountData::STATUS_INACTIVE), "=", AccountData::STATUS_INACTIVE);
                break;
            case "suspended":
                $memberSearch = $memberSearch->where(DB::raw("status&" . AccountData::STATUS_NETWORK_SUSPENDED), "=", AccountData::STATUS_NETWORK_SUSPENDED);
                break;
            case "nondivision":
                $memberSearch = \Models\Mship\Account\State::where("state", "!=", \Enums\Account\State::DIVISION)
                                                           ->with("account")
                                                           ->with("account.qualifications")
                                                           ->with("account.qualifications.qualification")
                                                           ->with("account.states")
                                                           ->with("account.emails")
                                                           ->orderBy("account_id", "ASC");
                break;
            case "division":
            default:
                $memberSearch = \Models\Mship\Account\State::where("state", "=", \Enums\Account\State::DIVISION)
                                                           ->with("account")
                                                           ->with("account.qualifications")
                                                           ->with("account.qualifications.qualification")
                                                           ->with("account.states")
                                                           ->with("account.emails")
                                                           ->orderBy("account_id", "ASC");
            break;
        }

        $memberSearch = $memberSearch->paginate(50);

        // Now we need to prepare the collection as a result for the view!
        $members = new Collection();
        foreach($memberSearch as $m) {
            $members->prepend(($m->account ? $m->account : $m));
        }
        $members = $members->reverse();


        return $this->viewMake("adm.mship.account.index")
                    ->with("members", $members)
                    ->with("membersQuery", $memberSearch)
                    ->with("sortBy", $sortBy)
                    ->with("sortDir", $sortDir)
                    ->with("sortDirSwitch", ($sortDir == "DESC" ? "ASC" : "DESC"));
    }

    public function getDetail(AccountData $account, $tab = "basic") {
        if(!$account OR $account->is_system) {
            return Redirect::route("adm.mship.account.index");
        }

        // Do they have permission to view their own profile?
        // This is to prevent people doing silly things....
        if($this->_account->account_id == $account->account_id && !$this->_account->hasPermission("adm/mship/account/own")) {
            return Redirect::route("adm.mship.account.index")
                           ->withError("You cannot view or manage your own profile.");
        }

        // Lazy eager loading
        $account->load(
            "notes", "notes.type", "notes.writer",
            "messagesReceived", "messagesReceived.sender",
            "messagesSent", "messagesSent.recipient",
            "dataChanges",
            "roles", "roles.permissions",
            "qualifications",
            "states",
            "emails",
            "security",
            "security.security"
        );

        // Get all possible roles!
        $availableRoles = RoleData::all()
                                  ->diff($account->roles);

        // Get all possible security levels.
        $securityLevels = SecurityData::all();

        // Get all possible note types.
        $noteTypes = NoteTypeData::usable()
                                 ->orderBy("name", "ASC")
                                 ->get();
        $noteTypesAll = NoteTypeData::withTrashed()
                                    ->orderBy("name", "ASC")
                                    ->get();

        $this->setTitle("Account Details: " . $account->name);

        return $this->viewMake("adm.mship.account.detail")
                    ->with("selectedTab", $tab)
                    ->with("account", $account)
                    ->with("availableRoles", $availableRoles)
                    ->with("securityLevels", $securityLevels)
                    ->with("noteTypes", $noteTypes)
                    ->with("noteTypesAll", $noteTypesAll);
    }

    public function postRoleAttach(AccountData $account) {
        if(!$account) {
            return Redirect::route("adm.mship.account.index");
        }

        // Let's try and load this RoleData
        $role = RoleData::find(Input::get("role"));

        if(!$role) {
            return Redirect::route("adm.mship.account.details", [$account->account_id], "role")
                           ->withError("The selected role does not exist.");
        }

        // Let's add!
        if(!$account->roles->contains($role->role_id)) {
            $account->roles()
                    ->attach($role);
        }

        return Redirect::route("adm.mship.account.details", [$account->account_id, "role"])
                       ->withSuccess($role->name . " role attached successfully. This user inherited " . count($role->permissions) . " permissions.");
    }

    public function postRoleDetach(AccountData $account, RoleData $role) {
        if(!$account) {
            return Redirect::route("adm.mship.account.index");
        }

        if(!$role) {
            return Redirect::route("adm.mship.account.details", [$account->account_id], "role")
                           ->withError("The selected role does not exist.");
        }

        if(!$account->roles->contains($role->role_id)) {
            return Redirect::route("adm.mship.account.details", [$account->account_id], "role")
                           ->withError("This role is not attached to this user.");
        }

        // Let's remove!
        $account->roles()
                ->detach($role);

        return Redirect::route("adm.mship.account.details", [$account->account_id, "role"])
                       ->withSuccess($role->name . " role detached successfully. This user lost " . count($role->permissions) . " permissions.");
    }

    public function postSecurityEnable(AccountData $account) {
        if(!$account) {
            return Redirect::route("adm.mship.account.index");
        }

        // Let's check the user doesn't currently have security on their account.
        // We don't want to just override it for no reason, as that's bad.
        $currentSecurity = $account->current_security;

        if($currentSecurity && $currentSecurity->exists) {
            return Redirect::route("adm.mship.account.details", [$account->account_id, "security"])
                           ->withError("You cannot enable security on this account.");
        }

        // Check the selected security ID exists!
        $security = SecurityData::find(Input::get("securityLevel", 0));

        if(!$security) {
            return Redirect::route("adm.mship.account.details", [$account->account_id, "security"])
                           ->withError("Invalid security ID specified.");
        }

        // Create them a blank security entry!
        $newSecurity = new AccountSecurityData();
        $newSecurity->save();
        $account->security()
                ->save($newSecurity);
        $security->accountSecurity()
                 ->save($newSecurity);

        return Redirect::route("adm.mship.account.details", [$account->account_id, "security"])
                       ->withSuccess("Security enabled for this account.");
    }

    public function postSecurityReset(AccountData $account) {
        if(!$account) {
            return Redirect::route("adm.mship.account.index");
        }

        // Let's check the user doesn't currently have security on their account.
        // We can't reset non-existant security!
        $currentSecurity = $account->current_security;

        if(!$currentSecurity OR !$currentSecurity->exists) {
            return Redirect::route("adm.mship.account.details", [$account->account_id, "security"])
                           ->withError("You cannot reset non-existant security.");
        }

        $account->resetPassword(true);

        return Redirect::route("adm.mship.account.details", [$account->account_id, "security"])
                       ->withSuccess("Security reset requested - user will receive an email.");
    }

    public function postSecurityChange(AccountData $account) {
        if(!$account) {
            return Redirect::route("adm.mship.account.index");
        }

        // Check the selected security ID exists!
        $security = SecurityData::find(Input::get("securityLevel", 0));

        if(!$security) {
            return Redirect::route("adm.mship.account.details", [$account->account_id, "security"])
                           ->withError("Invalid security ID specified.");
        }

        // Let's check the user doesn't currently have security on their account.
        // We don't want to just override it for no reason, as that's bad.
        $currentSecurity = $account->current_security;

        // It's also pointless changing to the same security ID.
        if(!$currentSecurity OR !$currentSecurity->exists OR $currentSecurity->security_id == $security->security_id) {
            return Redirect::route("adm.mship.account.details", [$account->account_id, "security"])
                           ->withError("You cannot change security on this account.");
        }

        // Let's expire the current security
        $currentSecurity->expire();
        $currentSecurity->delete();

        // Now, let's make a new one!
        $newSecurity = new AccountSecurityData();
        $newSecurity->save();
        $account->security()
                ->save($newSecurity);
        $security->accountSecurity()
                 ->save($newSecurity);

        return Redirect::route("adm.mship.account.details", [$account->account_id, "security"])
                       ->withSuccess("Security has been upgraded on this account.");
    }

    public function postNoteCreate(AccountData $account) {
        if(!$account) {
            return Redirect::route("adm.mship.account.index");
        }

        // Is there any content?
        if(strlen(Input::get("content")) < 10) {
            return Redirect::route("adm.mship.account.details", [$account->account_id, "notes"])
                           ->withError("You cannot add such a short note!");
        }

        // Check this type exists!
        $noteType = NoteTypeData::find(Input::get("note_type_id"));
        if(!$noteType OR !$noteType->exists) {
            return Redirect::route("adm.mship.account.details", [$account->account_id, "notes"])
                           ->withError("You selected an invalid note type.");
        }

        // Let's make a note and attach it to the user!
        $account->addNote($noteType->note_type_id, Input::get("content"), Auth::admin()
                                                                              ->get());

        return Redirect::route("adm.mship.account.details", [$account->account_id, "notes"])
                       ->withSuccess("The note has been saved successfully!");
    }

    public function postNoteFilter(AccountData $account) {
        if(!$account) {
            return Redirect::route("adm.mship.account.index");
        }

        // Get all filters
        $filters = Input::get("filter", []);
        $qs = "";
        foreach($filters as $f) {
            $qs .= "filter[" . $f . "]=1&";
        }

        return Redirect::to(URL::route("adm.mship.account.details", [$account->account_id, "notes"]) . "?" . $qs);
    }

    public function postImpersonate(AccountData $account) {
        if(!$account) {
            return Redirect::route("adm.mship.account.index");
        }

        TimelineEntryData::log("mship_account_impersonate", Auth::admin()
                                                                ->get(), $account, ["reason" => Input::get("reason")]);

        // Let's do the login!
        Auth::admin()
            ->impersonate("user", $account->account_id);
        Session::set("auth_override", true);

        return Redirect::to(URL::route("mship.manage.dashboard"))
                       ->withSuccess("You are now impersonating this user - your reason has been logged. Be good!");
    }
}
