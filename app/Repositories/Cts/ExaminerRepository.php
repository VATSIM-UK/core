<?php

namespace App\Repositories\Cts;

use App\Models\Cts\ExaminerSettings;

class ExaminerRepository
{
    public function getAtcExaminers()
    {
        $examiners = ExaminerSettings::with(['member'])
                                    ->where('OBS', '=', 1)
                                    ->orWhere('S1', '=', 1)
                                    ->orWhere('S2', '=', 1)
                                    ->orWhere('S3', '=', 1)
                                    ->orWhere('OBStrain', '=', 1)
                                    ->orWhere('S1train', '=', 1)
                                    ->orWhere('S2train', '=', 1)
                                    ->orWhere('S3train', '=', 1)
                                    ->get();

        return $examiners->map(function ($examiner) {
            return $examiner->member->cid;
        })->sort()->values();
    }

    public function getPilotExaminers()
    {
        $examiners = ExaminerSettings::with(['member'])
                                    ->orWhere('P1', '=', 1)
                                    ->orWhere('P2', '=', 1)
                                    ->orWhere('P3', '=', 1)
                                    ->orWhere('P4', '=', 1)
                                    ->orWhere('P5', '=', 1)
                                    ->orWhere('P1train', '=', 1)
                                    ->orWhere('P2train', '=', 1)
                                    ->orWhere('P3train', '=', 1)
                                    ->orWhere('P4train', '=', 1)
                                    ->orWhere('P5train', '=', 1)
                                    ->get();

        return $examiners->map(function ($examiner) {
            return $examiner->member->cid;
        })->sort()->values();
    }
}
