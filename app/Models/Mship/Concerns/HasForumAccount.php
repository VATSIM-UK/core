<?php

namespace App\Models\Mship\Concerns;

use App\Libraries\Forum;
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
        $forumService = app()->make(Forum::class);

        // Check forum enabled
        if (! $forumService->enabled()) {
            return;
        }

        $ipsAccount = $forumService->getIPSAccountForID($this->id);

        if (! $ipsAccount) {
            // No user. Abort;
            return;
        }

        // Set data
        DB::table("{$forumService->getDatabase()}.ibf_core_members")
            ->where('member_id', $ipsAccount->member_id)
            ->update([
                'name' => $this->name,
                'email' => $this->getEmailForService($forumService->getOauthClient()->id),
                'member_title' => $this->primary_state->name,
                'temp_ban' => ($this->is_banned) ? -1 : 0,
            ]);

        DB::table("{$forumService->getDatabase()}.ibf_core_pfields_content")
            ->where('member_id', $ipsAccount->member_id)
            ->update([
                'field_12' => $this->id, // VATSIM CID
                'field_13' => $this->qualification_atc->name_long, // Controller Rating
                'field_14' => $this->qualifications_pilot_string, // Pilot Ratings
            ]);
    }
}
