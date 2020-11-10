<?php

namespace App\Console\Commands\ExternalServices;

use App\Console\Commands\Command;
use App\Models\Mship\Account;
use DB;
use Exception;

class SyncCommunity extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'Sync:Community
                        {--f|force=0 : If specified, only this CID will be checked.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync membership data from Core to Community.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->option('verbose')) {
            $verbose = true;
        } else {
            $verbose = false;
        }

        require_once config('services.community.init_file');
        require_once \IPS\ROOT_PATH.'/system/Member/Member.php';
        require_once \IPS\ROOT_PATH.'/system/Db/Db.php';

        $members = \IPS\Db::i()->select(
            'm.member_id, m.temp_ban, l.token_identifier, m.name, m.email, m.member_title, p.field_12, p.field_13, p.field_14',
            ['core_members', 'm']
        )->join(['core_login_links', 'l'], 'm.member_id = l.token_member')
            ->join(['core_pfields_content', 'p'], 'm.member_id = p.member_id');

        $countTotal = $members->count();
        $countSuccess = 0;
        $countFailure = 0;

        $sso_account_id = DB::table('oauth_clients')->where('name', 'Community')->first()->id;
        for ($i = 0; $i < $countTotal; $i++) {
            $members->next();

            $member = $members->current();

            if (empty($member['token_identifier']) || ! is_numeric($member['token_identifier'])) {
                if ($verbose) {
                    $this->output->writeln('<error>FAILURE: '.$member['member_id'].' has no valid CID.</error>');
                }
                $countFailure++;
                continue;
            }

            if ($verbose) {
                $this->output->write($member['member_id'].' // '.$member['token_identifier']);
            }

            $member_core = Account::where('id', $member['token_identifier'])->with('states', 'qualifications')->first();
            if ($member_core === null) {
                if ($verbose) {
                    $this->output->writeln(' // <error>FAILURE: cannot retrieve member '.$member['member_id'].' from Core.</error>');
                }
                $countFailure++;
                continue;
            }

            $email = $member_core->email;
            $ssoEmailAssigned = $member_core->ssoEmails->filter(function ($ssoemail) use ($sso_account_id) {
                return $ssoemail->sso_account_id == $sso_account_id;
            })->values();

            if ($ssoEmailAssigned && count($ssoEmailAssigned) > 0) {
                $email = $ssoEmailAssigned[0]->email->email;
            }

            $emailLocal = false;
            if (empty($email)) {
                $email = $member['email'];
                $emailLocal = true;
            }

            $state = $member_core->primary_state->name;
            $aRatingString = $member_core->qualification_atc->name_long;
            $pRatingString = $member_core->qualifications_pilot_string;
            $pBanned = $member_core->is_banned;

            // Check for changes
            $changeEmail = strcasecmp($member['email'], $email);
            $changeName = strcmp($member['name'], $member_core->name);
            $changeState = strcasecmp($member['member_title'], $state);
            $changeCID = strcmp($member['field_12'], $member_core->id);
            $changeARating = strcmp($member['field_13'], $aRatingString);
            $changePRating = strcmp($member['field_14'], $pRatingString);

            // Ban Status Change
            $changeBan = false;
            if ($pBanned && ($member['temp_ban'] != -1)) {
                $changeBan = true;
            } elseif (! $pBanned && ($member['temp_ban'] != 0)) {
                $changeBan = true;
            }

            $changesPending = $changeEmail || $changeName || $changeState || $changeCID
                || $changeARating || $changePRating || $changeBan;

            if ($verbose) {
                $this->output->write(' // ID: '.$member_core->id);
                $this->output->write(' // Email ('.($emailLocal ? 'local' : 'latest').'):'.$email.($changeEmail ? '(changed)' : ''));
                $this->output->write(' // Display: '.$member_core->name.($changeName ? '(changed)' : ''));
                $this->output->write(' // State: '.$state.($changeState ? '(changed)' : ''));
                $this->output->write(' // ATC rating: '.$aRatingString);
                $this->output->write(' // Pilot ratings: '.$pRatingString);
            }

            $ips_member = \IPS\Member::load($member['member_id']);

            if ($changesPending) {
                try {
                    // ActiveRecord / Member fields
                    $ips_member->name = $member_core->name;
                    $ips_member->email = $email;
                    $ips_member->member_title = $state;
                    // Check/set bans
                    if (! $member_core->is_banned && $ips_member->temp_ban == -1) {
                        $ips_member->temp_ban = 0;
                    } elseif ($member_core->is_banned && $ips_member->temp_ban == 0) {
                        $ips_member->temp_ban = -1;
                    }
                    $ips_member->save();

                    // Profile fields (raw update)
                    $update = [
                        'field_12' => $member_core->id, // VATSIM CID
                        'field_13' => $aRatingString, // Controller Rating
                        'field_14' => $pRatingString, // Pilot Ratings
                    ];
                    $updated_rows = \IPS\Db::i()->update('core_pfields_content', $update, ['member_id=?', $member['member_id']]);

                    if ($verbose) {
                        $this->output->writeln(' // Details saved successfully.');
                    }
                    $countSuccess++;
                } catch (Exception $e) {
                    $countFailure++;
                    $this->output->writeln(' // <error>FAILURE: Error saving '.$member_core->id.' details to forum.</error>'.$e->getMessage());
                }
            } elseif ($verbose) {
                $this->output->writeln(' // No changes required.');
            }
        }

        if ($verbose) {
            $this->output->writeln('Run Results:');
            $this->output->writeln('Total Accounts: '.$countTotal);
            $this->output->writeln('Successful Updates: '.$countSuccess);
            $this->output->writeln('Failed Updates: '.$countFailure);
        }
    }
}
