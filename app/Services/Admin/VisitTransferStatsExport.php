<?php

namespace App\Services\Admin;

use Carbon\Carbon;

class VisitTransferStatsExport
{
    public static function build(?int $type, Carbon $start, Carbon $end, int $year, int $quarter): string
    {
        $breakdown = VisitTransferStats::byRating($type, $start, $end);
        $waiting = VisitTransferStats::currentlyWaitingByRating($type);

        $headers = ['Rating', 'Under Review', 'In Progress (manual)', 'Accepted', 'Rejected', 'Cancelled', 'Currently Waiting', 'Total'];
        $csvData = implode(',', $headers)."\n";

        $columnTotals = array_fill_keys(['under_review', 'accepted', 'rejected', 'cancelled', 'waiting'], 0);
        $grandTotal = 0;

        foreach ($breakdown as $r) {
            $waitingCount = $waiting[$r['name']]['waiting'] ?? 0;

            $rowTotal = $r['under_review'] + $r['accepted'] + $r['rejected'] + $r['cancelled'] + $waitingCount;

            $csvData .= implode(',', [
                $r['name'],
                $r['under_review'],
                '[Insert manually]',
                $r['accepted'],
                $r['rejected'],
                $r['cancelled'],
                $waitingCount,
                $rowTotal,
            ])."\n";

            $columnTotals['under_review'] += $r['under_review'];
            $columnTotals['accepted'] += $r['accepted'];
            $columnTotals['rejected'] += $r['rejected'];
            $columnTotals['cancelled'] += $r['cancelled'];
            $columnTotals['waiting'] += $waitingCount;
            $grandTotal += $rowTotal;
        }

        $csvData .= implode(',', [
            'Total',
            $columnTotals['under_review'],
            '',
            $columnTotals['accepted'],
            $columnTotals['rejected'],
            $columnTotals['cancelled'],
            $columnTotals['waiting'],
            $grandTotal,
        ])."\n";

        $csvData .= "\n";
        $csvData .= 'Year: '.$year.($quarter ? ", Quarter: {$quarter}" : ' (All Quarters)')."\n";

        return $csvData;
    }
}
