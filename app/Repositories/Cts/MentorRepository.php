<?php

namespace App\Repositories\Cts;

use App\Models\Cts\PositionValidation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MentorRepository
{
    public function getMentorsWithin(int $rtsId): Collection
    {
        $positionValidations = PositionValidation::with(['member', 'position'])
        ->whereHas('position', function (Builder $query) use ($rtsId) {
            $query->where('rts_id', '=', $rtsId);
        })->where('status', '=', 5)->get();

        $mentors = collect();

        foreach ($positionValidations as $positions) {
            $mentors->push($positions->member);
        }

        return $this->format($mentors->unique());
    }

    public function getMentorsFor(string $string): Collection
    {
        $positionValidations = PositionValidation::with(['member', 'position'])
        ->whereHas('position', function (Builder $query) use ($string) {
            $query->where('callsign', 'like', "{$string}%");
        })->where('status', '=', 5)->get();

        $mentors = collect();

        foreach ($positionValidations as $positions) {
            $mentors->push($positions->member);
        }

        return $this->format($mentors->unique());
    }

    private function format(Collection $data)
    {
        return $data->pluck('cid');
    }
}
