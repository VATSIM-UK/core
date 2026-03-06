<?php

namespace App\Services\VisitTransfer;

use App\Models\Mship\Account;

class DashboardService
{
    public function getDashboardData(Account $account): array
    {
        return [
            'allApplications' => $account->visitTransferApplications,
            'currentVisitApplication' => $account->visitTransferApplications()->visit()->open()->latest()->first(),
            'currentTransferApplication' => $account->visitTransferApplications()->transfer()->open()->latest()->first(),
            'pendingReferences' => $account->visitTransferReferee->filter(function ($reference) {
                return $reference->is_requested;
            }),
        ];
    }
}
