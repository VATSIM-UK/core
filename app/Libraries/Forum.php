<?php

namespace App\Libraries;

use Illuminate\Support\Facades\DB;

class Forum
{
    private ?string $database;

    private mixed $oauth_client;

    public function __construct()
    {
        $this->database = config('services.community.database');
        $this->oauth_client = DB::table('oauth_clients')->where('name', 'Community')->first();
    }

    /**
     * Returns whether the forum integration is enabled.
     *
     * @return bool
     */
    public function enabled()
    {
        return $this->database && $this->oauth_client;
    }

    /**
     * Fetches the IPB (forum) database model for a given local user ID (CID).
     *
     * @param  int  $user_id
     * @return \Illuminate\Database\Eloquent\Model|object|static|null
     */
    public function getIPSAccountForID($user_id)
    {
        return DB::table("{$this->database}.ibf_core_members")
            ->join("{$this->database}.ibf_core_login_links", 'ibf_core_login_links.token_member', '=', 'ibf_core_members.member_id')
            ->where('ibf_core_login_links.token_identifier', $user_id)
            ->first();
    }

    /**
     * Returns the database name for the IPB database.
     *
     * @return string|null
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Returns the local OAuth client used by the forum for authentication.
     *
     * @return \Illuminate\Database\Eloquent\Model|object|static|null
     */
    public function getOauthClient()
    {
        return $this->oauth_client;
    }
}
