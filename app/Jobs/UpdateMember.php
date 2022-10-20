<?php

namespace App\Jobs;

use App\Events\Mship\AccountAltered;
use App\Jobs\Middleware\RateLimited;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification as QualificationData;
use Carbon\Carbon;
use DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class UpdateMember extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    protected $accountID;
    protected $data;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 10;

    /**
     * The maximum number of exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 1;

    /**
     * Create a new job instance.
     */
    public function __construct($accountID)
    {
        $this->accountID = $accountID;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $member = Account::firstOrNew([(new Account)->getKeyName() => $this->accountID]);

        $token = 'Token '.config('vatsim-api.key');
        $url = config('vatsim-api.base')."members/{$this->accountID}";

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->get($url)->json();

            $this->data = (object) [
                'name_last' => $response['name_last'],
                'name_first' => $response['name_first'],
                'email' => $response['email'],
                'rating' => (string) $response['rating'],
                'regdate' => Carbon::parse($response['reg_date'])->toDateTimeString(),
                'pilotrating' => (string) $response['pilotrating'],
                'country' => null,
                'region' => $response['region_id'],
                'division' => $response['division_id'],
                'atctime' => (string) 0,
                'pilottime' => (string) 0,
                'cid' => $response['id'],
            ];
        } catch (\Exception $e) {
            return;
        }

        DB::beginTransaction();

        if (! is_string($this->data->region)) {
            $this->data->region = '';
        }

        if (! is_string($this->data->division)) {
            $this->data->division = '';
        }

        // if member no longer exists, delete
        // else process update
        if ($member && $this->data->name_first == new \stdClass()
            && $this->data->name_last == new \stdClass()
            && $this->data->email == '[hidden]'
        ) {
            $member->delete();
        } else {
            if (! empty($this->data->name_first) && is_string($this->data->name_first)) {
                $member->name_first = $this->data->name_first;
            }

            if (! empty($this->data->name_last) && is_string($this->data->name_last)) {
                $member->name_last = $this->data->name_last;
            }

            $member->cert_checked_at = Carbon::now();
            $member->is_inactive = (bool) ($this->data->rating < 0);

            if ($this->data->regdate !== '0000-00-00 00:00:00' && $this->data->regdate !== 'None') {
                $member->joined_at = $this->data->regdate;
            }

            $member->save();

            $state = determine_mship_state_from_vatsim($this->data->region, $this->data->division);
            $member->addState($state, $this->data->region, $this->data->division);

            $member = $this->processBans($member);
            $member = $this->processRating($member);

            $member->save();

            event(new AccountAltered($member));
        }

        DB::commit();
    }

    protected function processBans($member)
    {
        // if their network ban needs adding
        if ($this->data->rating == 0 && $member->is_network_banned === false) {
            // Add a ban.
            $newBan = new Account\Ban();
            $newBan->type = Account\Ban::TYPE_NETWORK;
            $newBan->reason_extra = 'Network ban discovered via Cert update scripts.';
            $newBan->period_start = Carbon::now();
            $member->bans()->save($newBan);
        }

        // if their network ban has expired
        if ($member->is_network_banned === true && $this->data->rating != 0) {
            $ban = $member->network_ban;
            $ban->period_finish = Carbon::now();
            $ban->save();
        }

        return $member;
    }

    protected function processRating($member)
    {
        // if they have an extra rating, log their previous rating
        if ($this->data->rating >= 8) {
            // This user has an admin rating but there is currently no support
            // for fetching their real rating via the VATSIM API. For
            // reference, the old AT code is below.

            // $_prevRat = VatsimXML::getData($member->id, 'idstatusprat');
            // if (isset($_prevRat->PreviousRatingInt)) {
            //     $prevAtcRating = QualificationData::parseVatsimATCQualification($_prevRat->PreviousRatingInt);
            //     if (! is_null($prevAtcRating) && ! $member->hasQualification($prevAtcRating)) {
            //         $member->addQualification($prevAtcRating);
            //     }
            // }
        } else {
            // remove any extra ratings
            foreach ($member->qualifications_atc_training as $qual) {
                $qual->pivot->delete();
            }
            foreach ($member->qualifications_pilot_training as $qual) {
                $qual->pivot->delete();
            }
            foreach ($member->qualifications_admin as $qual) {
                $qual->pivot->delete();
            }
        }

        // log their current rating (unless they're a non-UK instructor)
        if (($this->data->rating != 8 && $this->data->rating != 9) || $member->hasState('DIVISION')) {
            $atcRating = QualificationData::parseVatsimATCQualification($this->data->rating);
            if (! is_null($atcRating) && ! $member->hasQualification($atcRating)) {
                $member->addQualification($atcRating);
            } elseif (is_null($atcRating) && ! $member->qualification_atc) {
                // if we cannot find their ATC raiting and they don't have one already, set OBS
                $atcRating = QualificationData::parseVatsimATCQualification(1);
                $member->addQualification($atcRating);
            }
        }

        $pilotRatings = QualificationData::parseVatsimPilotQualifications($this->data->pilotrating);
        foreach ($pilotRatings as $pr) {
            if (! $member->hasQualification($pr)) {
                $member->addQualification($pr);
            }
        }

        return $member;
    }

    public function middleware()
    {
        return [new RateLimited('update_member_job', 1000, 60, 60)];
    }
}
