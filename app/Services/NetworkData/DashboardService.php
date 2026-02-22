<?php

namespace App\Services\NetworkData;

use App\Models\Account;

class DashboardService
{
    public function getDashboardData(Account $account): array
    {
        return [
            'atcSessions' => $account->networkDataAtc()->offline()->orderBy('created_at', 'DESC')->paginate(20, ['*'], 'atcSessions'),
            'pilotSessions' => $account->networkDataPilot()->offline()->orderBy('created_at', 'DESC')->paginate(20, ['*'], 'pilotSessions'),
        ];
    }
}
