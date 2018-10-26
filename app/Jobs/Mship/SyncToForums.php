<?php

namespace App\Jobs\Mship;

use App\Models\Mship\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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
        require_once config('services.community.init_file');
        require_once \IPS\ROOT_PATH.'/system/Member/Member.php';
        require_once \IPS\ROOT_PATH.'/system/Member/Club/Club.php';
        require_once \IPS\ROOT_PATH.'/system/Db/Db.php';

        $ips_account = \IPS\Db::i()->select(
            'm.member_id, m.temp_ban, l.token_identifier, m.name, m.email, m.member_title, p.field_12, p.field_13, p.field_14',
            ['core_members', 'm'])
            ->where('m.member_id = '.$this->account->id)
            ->join(['core_login_links', 'l'], 'm.member_id = l.token_member')
            ->join(['core_pfields_content', 'p'], 'm.member_id = p.member_id');

        if ($ips_account->count() == 0) {
            // No user. Abort;
            return;
        }

        $ips_account = \IPS\Member::load($this->account->id);

        // Set data
        $ips_account->name = $this->account->real_name;
        $ips_account->email = $this->account->getEmailForService(DB::table('oauth_clients')->where('name', 'Community')->first()->id);
        $ips_account->member_title = $this->account->primary_state->name;
        $ips_account->temp_ban = ($this->account->is_banned) ? -1 : 0;
        $ips_account->save();

        // Set profile data
        $update = [
            'field_12' => $this->account->id, // VATSIM CID
            'field_13' => $this->account->qualification_atc->name_long, // Controller Rating
            'field_14' => $this->account->qualifications_pilot_string, // Pilot Ratings
        ];
        \IPS\Db::i()->update('core_pfields_content', $update, ['member_id=?', $this->account->id]);

        // Set clubs
        $groups = $this->account->communityGroups()->notDefault()->get(['name']);
        $ips_clubs = \IPS\Db::i()->select('id,name', 'core_clubs');
        $club_map = [];
        for ($i = 0; $i < $ips_clubs->count(); $i++) {
            $ips_clubs->next();
            $club = $ips_clubs->current();
            $club_map[$club['id']] = $club['name'];
        }

        // Proccess core group membership.
        foreach ($groups as $group) {
            $ips_club_id = array_search($group->name, $club_map);
            if ($ips_club_id !== false) {
                $club = \IPS\Member\Club::load($ips_club_id);

                // Only add the user if not in already
                if ($club->memberStatus($ips_account) === null) {
                    $club->addMember($ips_account);
                }
            }
        }

        // Proccess member's IPB-side Club membership.
        foreach ($ips_account->clubs() as $ips_member_club) {
            $name = $club_map[$ips_member_club];

            if ($groups->pluck('name')->search($name) === false) {
                $ips_member_club = \IPS\Member\Club::load($ips_member_club);
                if (!$ips_member_club->isLeader($ips_account) && !$ips_member_club->isModerator($ips_account)) {
                    $ips_member_club->removeMember($ips_account);
                }
            }
        }

        Log::info($this->account->real_name.' synced to Forums');
    }
}
