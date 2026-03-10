<?php

namespace App\Services\Admin;

use App\Models\Mship\Account\Endorsement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ATCTrainingStats
{
    public static function completedMentoringSessions(Carbon $startDate, Carbon $endDate)
    {
        return DB::connection('cts')
            ->table('sessions')
            ->select('rts.name', DB::raw('count(*) as total'))
            ->join('rts', 'sessions.rts_id', '=', 'rts.id')
            ->whereBetween('taken_date', [$startDate, $endDate])
            ->whereNull('cancelled_datetime')
            ->where('noShow', '=', 0)
            ->groupBy('rts_id')
            ->get()
            ->flatMap(fn ($value) => [['name' => $value->name, 'value' => $value->total]])
            ->toArray();
    }

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

    public static function examPasses(Carbon $startDate, Carbon $endDate)
    {
        return DB::connection('cts')
            ->table('practical_results')
            ->select('exam', DB::raw('count(*) as total'))
            ->whereBetween('date', [$startDate, $endDate])
            ->where('result', '=', 'P')
            ->whereNotIn('exam', ['P1', 'P2', 'P3'])
            ->groupBy('exam')
            ->get()
            ->flatMap(fn ($value) => [['name' => $value->exam, 'value' => $value->total]])
            ->toArray();
    }

    public static function endorsementHolders(string $position)
{
    $sub = DB::table('mship_account_endorsement')
        ->join('position_groups', 'position_groups.id', '=', 'mship_account_endorsement.endorsable_id')
        ->join('mship_account_qualification', 'mship_account_qualification.account_id', '=', 'mship_account_endorsement.account_id')
        ->join('mship_qualification', function ($join) {
            $join->on('mship_qualification.id', '=', 'mship_account_qualification.qualification_id')
                ->where('mship_qualification.type', '=', 'atc');
        })
        ->where('position_groups.name', 'LIKE', "{$position}%")
        ->whereNull('mship_account_endorsement.deleted_at')
        ->whereNull('mship_account_qualification.deleted_at')
        ->selectRaw("
            mship_account_endorsement.account_id AS account_id,
            position_groups.name AS endorsement_name,
            mship_qualification.code AS rating,
            mship_qualification.vatsim AS vatsim,

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
            ) AS endorsement_rank,

            ROW_NUMBER() OVER (
                PARTITION BY mship_account_endorsement.account_id
                ORDER BY mship_qualification.vatsim DESC
            ) AS rating_rank
        ");

    return DB::query()
        ->fromSub($sub, 't')
        ->where('endorsement_rank', 1)
        ->where('rating_rank', 1)
        ->groupBy('rating', 'endorsement_name', 'vatsim')
        ->orderBy('vatsim', 'desc')
        ->selectRaw('rating, endorsement_name, vatsim, COUNT(*) AS total')
        ->get()
        ->groupBy('rating')
        ->map(fn ($group, $rating) => [
            'rating' => $rating,
            'endorsements' => $group->map(fn ($item) => [
                'endorsement' => $item->endorsement_name,
                'count' => $item->total,
            ])->values()->toArray(),
        ])
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
            ->whereBetween('period_end', [$startDate, $endDate])
            ->first();

        if ($update) {
            return route('filament.app.resources.roster-updates.view', ['record' => $update->id]);
        }

        return null;

    }
}
