<?php

namespace App\Jobs\Mship;

use App\Models\Mship\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class SyncToForums implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $account;

    public function __construct(Account $account)
    {
        $this->account = $account;
    }

    public function handle()
    {
        $communityClient = DB::table('oauth_clients')->where('name', 'Community')->first();
        if (!config('services.community.init_file') || $communityClient) {
            return;
        }
        require_once config('services.community.init_file');
        require_once \IPS\ROOT_PATH.'/system/Member/Member.php';
        require_once \IPS\ROOT_PATH.'/system/Member/Club/Club.php';
        require_once \IPS\ROOT_PATH.'/system/Db/Db.php';

        $ipsAccount = \IPS\Db::i()->select(
            'm.member_id, m.temp_ban, l.token_identifier, m.name, m.email, m.member_title, p.field_12, p.field_13, p.field_14',
            ['core_members', 'm'])
            ->where('m.member_id = '.$this->account->id)
            ->join(['core_login_links', 'l'], 'm.member_id = l.token_member')
            ->join(['core_pfields_content', 'p'], 'm.member_id = p.member_id');

        if ($ipsAccount->count() == 0) {
            // No user. Abort;
            return;
        }

        $ipsAccount = \IPS\Member::load($this->account->id);

        // Set data
        $ipsAccount->name = $this->account->real_name;
        $ipsAccount->email = $this->account->getEmailForService($communityClient->id);
        $ipsAccount->member_title = $this->account->primary_state->name;
        $ipsAccount->temp_ban = ($this->account->is_banned) ? -1 : 0;
        $ipsAccount->save();

        // Set profile data
        $update = [
            'field_12' => $this->account->id, // VATSIM CID
            'field_13' => $this->account->qualification_atc->name_long, // Controller Rating
            'field_14' => $this->account->qualifications_pilot_string, // Pilot Ratings
        ];
        \IPS\Db::i()->update('core_pfields_content', $update, ['member_id=?', $this->account->id]);

        // Set clubs
        $groups = $this->account->communityGroups()->notDefault()->get(['name']);
        $ipsClubs = \IPS\Db::i()->select('id,name', 'core_clubs');
        $clubMap = [];
        for ($i = 0; $i < $ipsClubs->count(); $i++) {
            $ipsClubs->next();
            $club = $ipsClubs->current();
            $clubMap[$club['id']] = $club['name'];
        }

        // Proccess core group membership.
        foreach ($groups as $group) {
            $ipsClubId = array_search($group->name, $clubMap);
            if ($ipsClubId !== false) {
                $club = \IPS\Member\Club::load($ipsClubId);

                // Only add the user if not in already
                if ($club->memberStatus($ipsAccount) === null) {
                    $club->addMember($ipsAccount);
                }
            }
        }

        // Proccess member's IPB-side Club membership.
        foreach ($ipsAccount->clubs() as $ipsMemberClub) {
            $name = $clubMap[$ipsMemberClub];

            if ($groups->pluck('name')->search($name) === false) {
                $ipsMemberClub = \IPS\Member\Club::load($ipsMemberClub);
                if (!$ipsMemberClub->isLeader($ipsAccount) && !$ipsMemberClub->isModerator($ipsAccount)) {
                    $ipsMemberClub->removeMember($ipsAccount);
                }
            }
        }
    }
}
