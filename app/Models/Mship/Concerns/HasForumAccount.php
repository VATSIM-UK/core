<?php

namespace App\Models\Mship\Concerns;

use Illuminate\Support\Facades\DB;

/**
 * Trait HasForumAccount.
 */
trait HasForumAccount
{
    /**
     * Sync the current account to the Forum.
     */
    public function syncToForum()
    {
        // Check forum enabled
        $communityClient = DB::table('oauth_clients')->where('name', 'Community')->first();
        $communityDb = config('services.community.database');

        if (! $communityDb || ! $communityClient) {
            return;
        }

        $ipsAccount = DB::table("{$communityDb}.ibf_core_members")
            ->join("{$communityDb}.ibf_core_login_links", 'ibf_core_login_links.token_member', '=', 'ibf_core_members.member_id')
            ->where('ibf_core_login_links.token_identifier', $this->id)
            ->first();

        if (! $ipsAccount) {
            // No user. Abort;
            return;
        }

        // Set data
        DB::table("{$communityDb}.ibf_core_members")
            ->where('member_id', $ipsAccount->member_id)
            ->update([
                'name' => $this->name,
                'email' => $this->getEmailForService($communityClient->id),
                'member_title' => $this->primary_state->name,
                'temp_ban' => ($this->is_banned) ? -1 : 0,
            ]);

        DB::table("{$communityDb}.ibf_core_pfields_content")
            ->where('member_id', $ipsAccount->member_id)
            ->update([
                'field_12' => $this->id, // VATSIM CID
                'field_13' => $this->qualification_atc->name_long, // Controller Rating
                'field_14' => $this->qualifications_pilot_string, // Pilot Ratings
            ]);
    }
}
