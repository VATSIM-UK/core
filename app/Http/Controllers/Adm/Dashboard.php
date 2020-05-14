<?php

namespace App\Http\Controllers\Adm;

use App\Models\Mship\Account;
use App\Models\Mship\Account\Email as AccountEmail;
use Cache;
use Illuminate\Support\Facades\Request;
use Redirect;

class Dashboard extends \App\Http\Controllers\Adm\AdmController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        return $this->viewMake('adm.dashboard');
    }

    public function anySearch($searchQuery = null)
    {
        if ($searchQuery == null) {
            $searchQuery = Request::input('q', null);
        }

        if (strlen($searchQuery) < 2 or $searchQuery == null) {
            return Redirect::route('adm.index');
        }

        // Direct member?
        if (is_numeric($searchQuery) && Account::find($searchQuery)) {
            return Redirect::route('adm.mship.account.details', [$searchQuery]);
        }

        // Global searches!
        $members = Cache::remember("adm_dashboard_membersearch_{$searchQuery}", 60 * 60, function () use ($searchQuery) {
            return Account::where('id', 'LIKE', '%'.$searchQuery.'%')
                ->orWhere(\DB::raw("CONCAT(`name_first`, ' ', `name_last`)"), 'LIKE', '%'.$searchQuery.'%')
                ->limit(25)
                ->get();
        });

        $emails = Cache::remember("adm_dashboard_emailssearch_{$searchQuery}", 60 * 60, function () use ($searchQuery) {
            return AccountEmail::where('email', 'LIKE', '%'.$searchQuery.'%')
                ->limit(25)
                ->get();
        });

        $this->setTitle('Global Search Results: '.$searchQuery);

        return $this->viewMake('adm.search')
            ->with('members', $members)
            ->with('emails', $emails);
    }
}
