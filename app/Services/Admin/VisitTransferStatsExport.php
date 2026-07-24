<?php

namespace App\Services\Admin;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class VisitTransferStatsExport
{
    public static function build(?int $type, Carbon $start, Carbon $end, int $year, int $quarter): string
    {
        $breakdown = VisitTransferStats::byRating($type, $start, $end);
        $waiting = VisitTransferStats::currentlyWaitingByRating($type);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('VT Statistics');

        $headers = ['Rating', 'Under Review', 'In Progress (manual)', 'Accepted', 'Rejected', 'Cancelled', 'Currently Waiting', 'Total'];
        $sheet->fromArray($headers, null, 'A1');
        // Style header row
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DDDDDD');

        $row = 2;

        foreach ($breakdown as $r) {
            $waitingCount = $waiting[$r['name']]['waiting'] ?? 0;

            $sheet->fromArray([
                $r['name'],
                $r['under_review'],
                $r['in_progress'],
                $r['accepted'],
                $r['rejected'],
                $r['cancelled'],
                $waitingCount,
                null, // filled by formula below
            ], null, "A{$row}");

            // Total per row
            $sheet->setCellValue("H{$row}", "=SUM(B{$row}:G{$row})");

            // Manual until we move VT into training place system
            $sheet->setCellValue("C{$row}", '[Insert manually]');

            $row++;
        }

        $lastDataRow = $row - 1;

        // Total per column
        $sheet->setCellValue("A{$row}", 'Total');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        foreach (['B', 'C', 'D', 'E', 'F', 'G', 'H'] as $col) {
            $sheet->setCellValue("{$col}{$row}", "=SUM({$col}2:{$col}{$lastDataRow})");
            $sheet->getStyle("{$col}{$row}")->getFont()->setBold(true);
        }

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->setCellValue('A'.($row + 2), "Year: {$year}".($quarter ? ", Quarter: {$quarter}" : ' (All Quarters)'));

        $writer = new Xlsx($spreadsheet);
        $path = storage_path('app/tmp/vt-stats-'.now()->timestamp.'.xlsx');
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        $writer->save($path);

        return $path;
    }
}
