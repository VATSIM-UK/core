<?php

namespace App\Console\Commands\Members;

use App\Console\Commands\Command;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Mship\State;
use App\Notifications\Mship\WelcomeMember;
use DB;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

/**
 * Utilizes the CERT divdb file to import new users and update existing user emails.
 */
class ImportMembers extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'Members:CertImport {--full}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import/update member emails from VATSIM API';

    protected $count_new = 0;
    protected $count_emails = 0;
    protected $count_none = 0;
    protected $member_list;
    protected $member_email_list;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->member_list = $this->getMemberIdAndEmail();

        foreach ($this->getMembers() as $member) {
            $this->log("Processing {$member['cid']} {$member['name_first']} {$member['name_last']}: ", null, false);

            DB::transaction(function () use ($member) {
                $this->processMember($member);
            });
        }
    }

    protected function getMembers()
    {
        $processResult = function (array $result) {
            return [
                'cid' => $result['id'],
                'rating_atc' => $result['rating'],
                'rating_pilot' => $result['pilotrating'],
                'name_first' => $result['name_first'],
                'name_last' => $result['name_last'],
                'email' => $result['email'],
                'age_band' => $result['age'],
                'city' => $result['countystate'],
                'country' => $result['country'],
                'experience' => '',
                'unknown' => '',
                'reg_date' => Carbon::parse($result['reg_date'])->toDateTimeString(),
                'region' => $result['region'],
                'division' => $result['division'],
            ];
        };

        // TODO: possibly add some OhDear functionality if this request fails?
        $url = config('vatsim-api.base').'divisions/GBR/members';
        $apiToken = config('vatsim-api.key');
        $response = Http::withHeaders([
            'Authorization' => "Token {$apiToken}",
        ])->get($url)->json();

        $memberCollection = collect();

        // process the first page of results.
        foreach ($response['results'] as $result) {
            $memberCollection->push($processResult($result));
        }

        // process any paginated results from the API.
        while ($response['next'] != null) {
            $response = Http::withHeaders([
                'Authorization' => "Token {$apiToken}",
            ])->get($response['next'])->json();

            foreach ($response['results'] as $result) {
                $memberCollection->push($processResult($result));
            }
        }

        return $memberCollection;
    }

    protected function processMember($member)
    {
        if (array_get($this->member_list, $member['cid'], 'unknown') != 'unknown') {
            if (strcasecmp($this->member_list[$member['cid']], $member['email']) !== 0) {
                $this->updateMember($member);
                $this->log('updated member');
                $this->count_emails++;

                return;
            }

            $this->log('no important changes required.');
            $this->count_none++;

            return;
        }

        $this->createNewMember($member);
        $this->log('created new account');
        $this->count_new++;
    }

    protected function createNewMember($member_data)
    {
        $validator = Validator::make($member_data, [
            'cid' => 'required|integer',
            'name_first' => 'required|string',
            'name_last' => 'required|string',
            'email' => 'required|email',
            'reg_date' => 'required|date',
            'rating_atc' => 'required|integer',
        ]);

        if ($validator->fails()) {
            // Incorrectly formatted response from CERT
            return;
        }

        $member = new Account([
            'id' => $member_data['cid'],
            'name_first' => $member_data['name_first'],
            'name_last' => $member_data['name_last'],
            'email' => $member_data['email'],
            'joined_at' => $member_data['reg_date'],
        ]);
        $member->is_inactive = (bool) ($member_data['rating_atc'] < 0);
        $member->save();

        $member->addState(State::findByCode('DIVISION'), 'EMEA', 'GBR');

        // if they have an extra rating, log their previous rating first,
        // regardless of whether it will be overwritten
        if ($member_data['rating_atc'] >= 8) {
            // This user has an admin rating but there is currently no support
            // for fetching their real rating via the VATSIM API. For
            // reference, the old AT code is below.

            // try {
            //     $_prevRat = VatsimXML::getData($member->id, 'idstatusprat');
            // } catch (Exception $e) {
            //     if (strpos($e->getMessage(), 'Name or service not known') !== false) {
            //         // CERT unavailable. Not our fault, so will ignore.
            //         return;
            //     }

            //     return;
            // }

            // if (isset($_prevRat->PreviousRatingInt)) {
            //     $prevAtcRating = Qualification::parseVatsimATCQualification($_prevRat->PreviousRatingInt);

            //     if ($prevAtcRating) {
            //         $member->addQualification($prevAtcRating);
            //     }
            // }
        }

        // if they're a division member, or their current rating isn't instructor, log their 'main' rating
        if (($member_data['rating_atc'] < 8) || $member->hasState('DIVISION')) {
            $atcRating = Qualification::parseVatsimATCQualification($member_data['rating_atc']);

            if ($atcRating) {
                $member->addQualification($atcRating);
            }
        }

        // anything else is processed by the Members:CertUpdate script

        if ($member->hasState('DIVISION') && $member->email) {
            $member->notify(new WelcomeMember());
        }
    }

    protected function updateMember($member_data)
    {
        $member = Account::find($member_data['cid']);
        $member->name_first = $member_data['name_first'];
        $member->name_last = $member_data['name_last'];
        $member->email = $member_data['email'];
        $member->save();

        $member->addState(State::findByCode('DIVISION'), 'EMEA', 'GBR');
    }

    protected function getMemberIdAndEmail()
    {
        return DB::table('mship_account')
            ->pluck('email', 'id');
    }
}
