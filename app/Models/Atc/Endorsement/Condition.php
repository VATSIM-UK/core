<?php

namespace App\Models\Atc\Endorsement;

use App\Models\Atc\Endorsement;
use App\Models\Mship\Account;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Condition extends Model
{
    protected $table = 'endorsement_conditions';

    protected $fillable = [
        'endorsement_id',
        'positions',
        'required_hours',
        'type',
        'within_months',
    ];

    protected $casts = [
        'positions' => 'array',
    ];

    private $progress;

    const TYPE_ON_SINGLE_AIRFIELD = 1; // To qualify, any of the member positions must have at least the given hours

    const TYPE_SUM_OF_AIRFIELDS = 2; // To qualify, the sum of hours across all qualifying positions must meet the given hours

    public function endorsement()
    {
        return $this->belongsTo(Endorsement::class);
    }

    public function getHumanPositionsAttribute()
    {
        return str_replace('%', 'XXX', $this->positions);
    }

    public function getHumanDescriptionAttribute()
    {
        $description = "<b>$this->required_hours hour".($this->required_hours > 1 ? 's </b>' : ' </b>');

        if ($this->within_months) {
            $description .= "within the last <b>$this->within_months month".($this->required_hours > 1 ? 's </b>' : ' </b>');
        }

        switch ($this->type) {
            case self::TYPE_ON_SINGLE_AIRFIELD:
                $description .= 'on <b>one of the qualifying positions</b>';
                break;
            case self::TYPE_SUM_OF_AIRFIELDS:
                $description .= 'on the <b>total hours across all qualifying positions</b>';
                break;
        }

        return $description;
    }

    public function isMetForUser(Account $user)
    {
        return $this->overallProgressForUser($user) >= $this->required_hours;
    }

    public function overallProgressForUser(Account $user)
    {
        $airfieldGroups = $this->progress ? $this->progress->shuffle() : $this->progressForUser($user)->shuffle();

        switch ($this->type) {
            case self::TYPE_ON_SINGLE_AIRFIELD:
                $max = 0;
                foreach ($airfieldGroups as $hours) {
                    if ($hours > $max) {
                        $max = $hours;
                    }
                }

                return $max;
            case self::TYPE_SUM_OF_AIRFIELDS:
                return $airfieldGroups->sum();
            default:
                return 0;
        }
    }

    public function progressForUser(Account $user)
    {
        // Find matching sessions
        $sessions = $user->networkDataAtc()
            ->withCallsignIn($this->positions);
        if ($this->within_months) {
            $sessions = $sessions->whereBetween('connected_at', [Carbon::now()->subMonths($this->within_months), Carbon::now()]);
        }

        if ($this->required_qualification) {
            $sessions = $sessions->where('qualification_id', $this->required_qualification);
        }

        $sessions = $sessions->get(['minutes_online', 'callsign']);

        // Map into groups of positions
        return $this->progress = $sessions->mapToGroups(function ($session) {
            // Attempt to find the base position (e.g ESSEX or EGKK)
            $split = explode('_', $session['callsign']);

            $index = count($split) == 1 ? $session['callsign'] : $split[0];

            return [$index => ($session['minutes_online'] / 60)];
        })->transform(function ($sessions) {
            return $sessions->sum();
        });
    }
}
