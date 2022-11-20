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
            })->mentors()->get();

        $mentors = collect();

        foreach ($positionValidations as $positions) {
            $mentors->push($positions->member);
        }

        return $this->format($mentors->unique());
    }

    public function getMentorsFor(string $search): Collection
    {
        $positionValidations = PositionValidation::with(['member', 'position'])
            ->whereHas('position', function (Builder $query) use ($search) {
                $query->where('callsign', 'like', "{$search}%");
            })->mentors()->get();

        $mentors = collect();

        foreach ($positionValidations as $positions) {
            $mentors->push($positions->member);
        }

        return $this->format($mentors->unique());
    }

    public function getMentorsForPositionType(string $positionSuffix): Collection
    {
        return $this->format(PositionValidation::with(['member'])
            ->whereHas('position', function (Builder $query) use ($positionSuffix) {
                $query->where('callsign', 'like', "%_$positionSuffix");
            })->mentors()->select('member_id')->distinct()->get()->map(fn ($position) => $position->member));
    }

    private function format(Collection $data)
    {
        return $data->pluck('cid')->transform(function ($item) {
            return (string) $item;
        })->sort()->values();
    }
}
