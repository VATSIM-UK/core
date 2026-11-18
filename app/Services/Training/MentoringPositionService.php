<?php

declare(strict_types=1);

namespace App\Services\Training;

use App\Libraries\UKCP;
use App\Models\Cts\Position as CtsPosition;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Illuminate\Support\Str;

class MentoringPositionService
{
    public function __construct(private readonly UKCP $ukcp) {}

    /**
     * Top-down positions below the given CTS position that can be booked for mentoring.
     *
     * @return list<string>
     */
    public function topDownCallsignsFor(string $parentCallsign): array
    {
        $parentCallsign = strtoupper(trim($parentCallsign));
        $topDown = $this->ukcp->getTopDownPositionsForPosition($parentCallsign);

        if ($topDown === null) {
            return [];
        }

        $suffix = Str::afterLast($parentCallsign, '_');

        $candidates = $topDown
            ->pluck('callsign')
            ->map(fn ($callsign): string => strtoupper(trim((string) $callsign)))
            ->filter(fn (string $callsign): bool => $callsign !== $parentCallsign
                && Str::afterLast($callsign, '_') === $suffix)
            ->unique()
            ->values()
            ->all();

        if ($candidates === []) {
            return [];
        }

        $callsigns = CtsPosition::query()
            ->whereIn('callsign', $candidates)
            ->pluck('callsign')
            ->all();

        sort($callsigns);

        return array_values(array_unique($callsigns));
    }

    public function isTopDownOf(string $parentCallsign, string $position): bool
    {
        return in_array(strtoupper(trim($position)), $this->topDownCallsignsFor($parentCallsign), true);
    }

    /**
     * Resolve the training position for a selected bookable position.
     *
     * @param  list<string>|null  $allowedParents
     */
    public function trainingPositionFor(TrainingPlace $trainingPlace, string $position, ?array $allowedParents = null): ?string
    {
        $position = strtoupper(trim($position));
        $parentCallsigns = $trainingPlace->trainableCtsPositions();

        if ($allowedParents !== null) {
            $parentCallsigns = array_values(array_intersect($parentCallsigns, $allowedParents));
        }

        foreach ($parentCallsigns as $parentCallsign) {
            if (strtoupper($parentCallsign) === $position) {
                return $parentCallsign;
            }
        }

        foreach ($parentCallsigns as $parentCallsign) {
            if ($this->isTopDownOf($parentCallsign, $position)) {
                return $parentCallsign;
            }
        }

        return null;
    }
}
