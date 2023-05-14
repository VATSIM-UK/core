<?php

namespace App\Repositories\Cts;

use App\Models\Cts\ExaminerSettings;

class ExaminerRepository
{
    public function getAtcExaminers()
    {
        $examiners = ExaminerSettings::with(['member'])
            ->atc()
            ->get();

        return $examiners->reject(function ($examiner) {
            return ! (bool) $examiner->member->examiner;
        })->map(function ($examiner) {
            return $examiner->member->cid;
        })->sort()->values();
    }

    public function getPilotExaminers()
    {
        $examiners = ExaminerSettings::with(['member'])
            ->pilot()
            ->get();

        return $examiners->reject(function ($examiner) {
            return ! (bool) $examiner->member->examiner;
        })->map(function ($examiner) {
            return $examiner->member->cid;
        })->sort()->values();
    }
}
