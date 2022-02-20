<?php

namespace App\Libraries;

use Illuminate\Support\Facades\DB;

class Forum
{
    private string $database;
    private $oauth_client;

    public function __construct()
    {
        $this->database = config('services.community.database');
        $this->oauth_client = DB::table('oauth_clients')->where('name', 'Community')->first();
    }

    public function enabled()
    {
        return $this->database && $this->oauth_client;
    }

    public function getIPSAccountForID($user_id)
    {
        return
            DB::table("{$this->database}.ibf_core_members")
            ->join("{$this->database}.ibf_core_login_links", 'ibf_core_login_links.token_member', '=', 'ibf_core_members.member_id')
            ->where('ibf_core_login_links.token_identifier', $user_id)
            ->first();
    }

    public function getDatabase()
    {
        return $this->database;
    }

    public function getOauthClient()
    {
        return $this->oauth_client
    }
}
