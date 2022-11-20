<?php

namespace App\Repositories\Cts;

use App\Models\Cts\PositionValidation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class StudentRepository
{
    public function getStudentsWithin(int $rtsId): Collection
    {
        $positionValidations = PositionValidation::with(['member', 'position'])
            ->whereHas('position', function (Builder $query) use ($rtsId) {
                $query->where('rts_id', '=', $rtsId);
            })->where('status', '=', 1)->get();

        $students = collect();

        foreach ($positionValidations as $positions) {
            $students->push($positions->member);
        }

        return $this->format($students->unique());
    }

    public function getStudentsWithRequestPermissionsFor(string $callsign): Collection
    {
        $students = PositionValidation::with(['member', 'position'])
            ->whereHas('position', function (Builder $query) use ($callsign) {
                return $query->where('callsign', $callsign);
            })
            ->students()
            ->get()
            ->map(function ($position) {
                return $position->member;
            });

        return $this->format($students->unique());
    }

    public function getStudentsWithRequestPermissionsForType(string $positionSuffix): Collection
    {
        return $this->format(PositionValidation::with(['member'])
            ->whereHas('position', function (Builder $query) use ($positionSuffix) {
                $query->where('callsign', 'like', "%_$positionSuffix");
            })->students()->select('member_id')->distinct()->get()->map(fn ($position) => $position->member));
    }

    private function format(Collection $data)
    {
        return $data->pluck('cid')->transform(function ($item) {
            return (string) $item;
        })->sort()->values();
    }
}
