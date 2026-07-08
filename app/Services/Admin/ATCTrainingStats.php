<?php

namespace App\Services\Admin;

use App\Models\Mship\Account\Endorsement;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\RemovalReason;
use App\Models\Training\WaitingList\WaitingListAccount;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ATCTrainingStats
{
    public static function issuedPositionGroupEndorsements(Carbon $startDate, Carbon $endDate)
    {
        return Endorsement::with('endorsable')
            ->whereBetween('mship_account_endorsement.created_at', [$startDate, $endDate])
            ->join('position_groups', 'position_groups.id', 'mship_account_endorsement.endorsable_id')
            ->groupBy('position_groups.id', 'position_groups.name')
            ->select(['position_groups.name', 'position_groups.id', DB::raw('count(*) as total')])
            ->get()
            ->flatMap(fn ($value) => [['name' => $value->name, 'value' => $value->total]])
            ->toArray();
    }

    public static function endorsementHolders(string $position)
    {
        $sub = DB::table('mship_account_qualification')
            ->join('mship_qualification', function ($join) {
                $join->on('mship_qualification.id', '=', 'mship_account_qualification.qualification_id')
                    ->where('mship_qualification.type', '=', 'atc');
            })
            ->whereNull('mship_account_qualification.deleted_at')
            ->selectRaw('
            mship_account_qualification.account_id,
            mship_qualification.code AS rating,
            mship_qualification.vatsim AS vatsim,
            ROW_NUMBER() OVER (
                PARTITION BY mship_account_qualification.account_id
                ORDER BY mship_account_qualification.created_at DESC
            ) AS rating_rank
        ');

        $endorsementRanked = DB::table('mship_account_endorsement')
            ->join('position_groups', 'position_groups.id', '=', 'mship_account_endorsement.endorsable_id')
            ->joinSub($sub, 'hr', function ($join) {
                $join->on('hr.account_id', '=', 'mship_account_endorsement.account_id')
                    ->where('hr.rating_rank', '=', 1);
            })
            ->where('position_groups.name', 'LIKE', "{$position}%")
            ->whereNull('mship_account_endorsement.deleted_at')
            ->selectRaw("
            mship_account_endorsement.account_id,
            hr.rating,
            position_groups.name AS endorsement,
            ROW_NUMBER() OVER (
                PARTITION BY mship_account_endorsement.account_id
                ORDER BY
                    CASE
                        WHEN position_groups.name LIKE '%CTR' THEN 4
                        WHEN position_groups.name LIKE '%APP' THEN 3
                        WHEN position_groups.name LIKE '%TWR' THEN 2
                        WHEN position_groups.name LIKE '%GND' THEN 1
                        ELSE 0
                    END DESC
            ) AS endorsement_rank
        ");

        $filtered = DB::query()
            ->fromSub($endorsementRanked, 'e')
            ->where('endorsement_rank', 1)
            ->get();

        return $filtered
            ->groupBy('rating')
            ->map(function ($group) {
                return [
                    'rating' => $group->first()->rating,
                    'endorsements' => $group
                        ->groupBy('endorsement')
                        ->map(fn ($items) => [
                            'endorsement' => $items->first()->endorsement,
                            'count' => $items->count(),
                        ])->values()->toArray(),
                ];
            })
            ->values()
            ->toArray();
    }

    public static function rosterCountByRating()
    {
        return DB::table('roster')
            ->join('mship_account_qualification', 'mship_account_qualification.account_id', '=', 'roster.account_id')
            ->join('mship_qualification', function ($join) {
                $join->on('mship_qualification.id', '=', 'mship_account_qualification.qualification_id')
                    ->where('mship_qualification.type', '=', 'atc');
            })
            ->whereNull('mship_account_qualification.deleted_at')
            ->whereRaw('mship_qualification.vatsim = (
            SELECT MAX(maxqual.vatsim)
            FROM mship_account_qualification mshipqual
            JOIN mship_qualification maxqual ON maxqual.id = mshipqual.qualification_id
            WHERE mshipqual.account_id = roster.account_id
            AND maxqual.type = ?
        )', ['atc'])
            ->groupBy('mship_qualification.code', 'mship_qualification.vatsim')
            ->select([
                'mship_qualification.code as rating',
                DB::raw('count(DISTINCT roster.account_id) as total'),
            ])
            ->orderBy('mship_qualification.vatsim', 'asc')
            ->get()
            ->map(fn ($row) => [
                'rating' => $row->rating,
                'count' => $row->total,
            ])
            ->toArray();
    }

    public static function rosterUpdateLink(Carbon $startDate, Carbon $endDate)
    {
        $update = DB::table('roster_updates')
            ->whereBetween('period_start', [$startDate, $endDate])
            ->first();

        if ($update) {
            return route('filament.app.resources.roster-updates.view', ['record' => $update->id]);
        }

        return null;

    }

    private static function getCallsignToCategoryMap(): array
    {
        $positions = DB::table('training_positions')
            ->whereNotNull('category')
            ->whereNotNull('cts_positions')
            ->get(['category', 'cts_positions']);

        $map = [];
        foreach ($positions as $tp) {
            $callsigns = json_decode($tp->cts_positions, true) ?? [];
            foreach ($callsigns as $callsign) {
                $map[$callsign] = $tp->category;
            }
        }

        return $map;
    }

    public static function completedMentoringSessionsByTG(Carbon $startDate, Carbon $endDate): array
    {
        $callsignToCategory = self::getCallsignToCategoryMap();

        $sessions = DB::connection('cts')
            ->table('sessions')
            ->select('position', DB::raw('count(*) as total'))
            ->whereBetween('taken_date', [$startDate, $endDate])
            ->whereNull('cancelled_datetime')
            ->where('noShow', '=', 0)
            ->groupBy('position')
            ->get();

        $categoryCounts = [];
        foreach ($sessions as $session) {
            $category = $callsignToCategory[$session->position] ?? 'Other';
            $categoryCounts[$category] = ($categoryCounts[$category] ?? 0) + $session->total;
        }

        ksort($categoryCounts);

        $result = collect($categoryCounts)
            ->map(fn ($count, $name) => ['name' => $name, 'value' => $count])
            ->values()
            ->toArray();
        $result[] = ['name' => 'Total', 'value' => array_sum($categoryCounts)];

        return $result;
    }

    public static function examsConductedByTG(Carbon $startDate, Carbon $endDate): array
    {
        $examToCategory = [
            'OBS' => 'OBS to S1 Training',
            'TWR' => 'S2 Training',
            'APP' => 'S3 Training',
            'CTR' => 'C1 Training',
        ];

        $results = DB::connection('cts')
            ->table('practical_results')
            ->select('exam', DB::raw('count(*) as total'))
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotIn('exam', ['P1', 'P2', 'P3'])
            ->groupBy('exam')
            ->get();

        $categoryCounts = [];
        foreach ($results as $result) {
            $category = $examToCategory[$result->exam] ?? $result->exam;
            $categoryCounts[$category] = ($categoryCounts[$category] ?? 0) + $result->total;
        }

        ksort($categoryCounts);

        $result = collect($categoryCounts)
            ->map(fn ($count, $name) => ['name' => $name, 'value' => $count])
            ->values()
            ->toArray();
        $result[] = ['name' => 'Total', 'value' => array_sum($categoryCounts)];

        return $result;
    }

    public static function ratingUpgradesByTG(Carbon $startDate, Carbon $endDate): array
    {
        $qualToCategory = [
            'S1' => 'OBS to S1 Training',
            'S2' => 'S2 Training',
            'S3' => 'S3 Training',
            'C1' => 'C1 Training',
        ];

        $upgrades = DB::table('mship_account_qualification')
            ->join('mship_qualification', 'mship_qualification.id', '=', 'mship_account_qualification.qualification_id')
            ->where('mship_qualification.type', '=', 'atc')
            ->whereBetween('mship_account_qualification.created_at', [$startDate, $endDate])
            ->whereNull('mship_account_qualification.deleted_at')
            ->select('mship_qualification.code')
            ->get();

        $categoryCounts = [];
        foreach ($upgrades as $upgrade) {
            $category = $qualToCategory[$upgrade->code] ?? null;

            if ($category) {
                $categoryCounts[$category] = ($categoryCounts[$category] ?? 0) + 1;
            }
        }

        ksort($categoryCounts);

        $result = collect($categoryCounts)
            ->map(fn ($count, $name) => ['name' => $name, 'value' => $count])
            ->values()
            ->toArray();
        $result[] = ['name' => 'Total', 'value' => array_sum($categoryCounts)];

        return $result;
    }

    public static function heathrowEndorsementsIssued(Carbon $startDate, Carbon $endDate): array
    {
        $endorsements = Endorsement::with('endorsable')
            ->whereBetween('mship_account_endorsement.created_at', [$startDate, $endDate])
            ->join('position_groups', 'position_groups.id', 'mship_account_endorsement.endorsable_id')
            ->whereIn('position_groups.name', ['Heathrow (TWR)', 'Heathrow (APP)', 'Heathrow (GND)'])
            ->groupBy('position_groups.id', 'position_groups.name')
            ->select(['position_groups.name', 'position_groups.id', DB::raw('count(*) as total')])
            ->get()
            ->flatMap(fn ($value) => [['name' => $value->name, 'value' => $value->total]])
            ->toArray();

        $total = collect($endorsements)->sum('value');
        $endorsements[] = ['name' => 'Total', 'value' => $total];

        return $endorsements;
    }

    public static function atcWaitingListCounts(): array
    {
        $lists = WaitingList::where('department', WaitingList::ATC_DEPARTMENT)->get();

        $counts = $lists->map(fn ($list) => [
            'name' => $list->name,
            'value' => $list->waitingListAccounts->count(),
        ])->toArray();

        $total = collect($counts)->sum('value');
        $counts[] = ['name' => 'Total', 'value' => $total];

        return $counts;
    }

    public static function atcWaitingListRemovals(Carbon $startDate, Carbon $endDate): array
    {
        $lists = WaitingList::where('department', WaitingList::ATC_DEPARTMENT)->get();
        $listIds = $lists->pluck('id');

        $removals = WaitingListAccount::onlyTrashed()
            ->whereIn('list_id', $listIds)
            ->where('removal_type', RemovalReason::Inactivity->value)
            ->whereBetween('deleted_at', [$startDate, $endDate])
            ->select('list_id', DB::raw('count(*) as total'))
            ->groupBy('list_id')
            ->get()
            ->keyBy('list_id');

        $counts = $lists->map(fn ($list) => [
            'name' => $list->name,
            'value' => $removals->get($list->id)?->total ?? 0,
        ])->toArray();

        $total = collect($counts)->sum('value');
        $counts[] = ['name' => 'Total', 'value' => $total];

        return $counts;
    }

    public static function mentorsByTG(Carbon $startDate, Carbon $endDate): array
    {
        $callsignToCategory = self::getCallsignToCategoryMap();

        $mentorSessions = DB::connection('cts')
            ->table('sessions')
            ->select('sessions.position', 'sessions.mentor_id')
            ->whereBetween('taken_date', [$startDate, $endDate])
            ->whereNull('cancelled_datetime')
            ->whereNotNull('mentor_id')
            ->where('noShow', '=', 0)
            ->whereIn('sessions.position', array_keys($callsignToCategory))
            ->get();

        $mentorsByCategory = [];
        foreach ($mentorSessions as $session) {
            $category = $callsignToCategory[$session->position] ?? null;
            if ($category) {
                $mentorsByCategory[$category][$session->mentor_id] = true;
            }
        }

        $result = collect($mentorsByCategory)
            ->map(fn ($mentors, $category) => ['name' => $category, 'value' => count($mentors)])
            ->sortBy('name')
            ->values()
            ->toArray();

        $total = collect($result)->sum('value');
        $result[] = ['name' => 'Total', 'value' => $total];

        return $result;
    }
}
