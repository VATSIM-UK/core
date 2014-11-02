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
use \Models\Mship\Account\Account as AccountData;

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

    public function postGoToCid(){
        $member = AccountData::find(Input::get("cid", 0));

        return Redirect::to("/adm/mship/account/".$member->account_id);
    }

    public function getDetail(AccountData $account) {
        if (!$account) {
            return Redirect::to("/adm/mship/account");
        }

        $this->_pageTitle = "Account Details: " . $account->name;

        return $this->viewMake("adm.mship.account.detail")
                        ->with("account", $account);
    }
}
