<?php

namespace App\Console\Commands;

use DB;
use App\Models\Mship\Account;

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

        require_once '/var/www/community/init.php';
        require_once \IPS\ROOT_PATH.'/system/Member/Member.php';
        require_once \IPS\ROOT_PATH.'/system/Db/Db.php';

        $members = \IPS\Db::i()->select('m.member_id, m.vatsim_cid, m.name, m.email, m.member_title, p.field_12, p.field_13, p.field_14', ['core_members', 'm'])
                               ->join(['core_pfields_content', 'p'], 'm.member_id = p.member_id');

        $countTotal = $members->count();
        $countSuccess = 0;
        $countFailure = 0;

        $sso_account_id = DB::table('sso_account')->where('username', 'vuk.community')->first()->id;
        for ($i = 0; $i < $countTotal; $i++) {
            $members->next();

            $member = $members->current();

            if (empty($member['vatsim_cid']) || !is_numeric($member['vatsim_cid'])) {
                if ($verbose) {
                    $this->output->writeln('<error>FAILURE: '.$member['member_id'].' has no valid CID.</error>');
                }
                $countFailure++;
                continue;
            }

            if ($verbose) {
                $this->output->write($member['member_id'].' // '.$member['vatsim_cid']);
            }

            $member_core = Account::where('id', $member['vatsim_cid'])->with('states', 'qualifications')->first();
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

            // Check for changes
            $changeEmail = strcasecmp($member['email'], $email);
            $changeName = strcmp($member['name'], $member_core->name_first.' '.$member_core->name_last);
            $changeState = strcasecmp($member['member_title'], $state);
            $changeCID = strcmp($member['field_12'], $member_core->id);
            $changeARating = strcmp($member['field_13'], $aRatingString);
            $changePRating = strcmp($member['field_14'], $pRatingString);
            $changesPending = $changeEmail || $changeName || $changeState || $changeCID
                              || $changeARating || $changePRating;

            if ($verbose) {
                $this->output->write(' // ID: '.$member_core->id);
                $this->output->write(' // Email ('.($emailLocal ? 'local' : 'latest').'):'.$email.($changeEmail ? '(changed)' : ''));
                $this->output->write(' // Display: '.$member_core->name_first.' '.$member_core->name_last.($changeName ? '(changed)' : ''));
                $this->output->write(' // State: '.$state.($changeState ? '(changed)' : ''));
                $this->output->write(' // ATC rating: '.$aRatingString);
                $this->output->write(' // Pilot ratings: '.$pRatingString);
            }

            if ($changesPending) {
                try {
                    // ActiveRecord / Member fields
                    $ips_member = \IPS\Member::load($member['member_id']);
                    $ips_member->name = $member_core->name_first.' '.$member_core->name_last;
                    $ips_member->email = $email;
                    $ips_member->member_title = $state;
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
