<?php

namespace App\Filament\Admin\Resources\AccountResource\Pages;

use App\Filament\Admin\Helpers\Pages\BaseListRecordsPage;
use App\Filament\Admin\Resources\AccountResource;
use App\Models\Mship\Account;

class ListAccounts extends BaseListRecordsPage
{
    protected static string $resource = AccountResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function updatedTableSearch(): void
    {
        $search = $this->getTableSearch();

        $match = Account::query()
            ->where('id', 'like', "%{$search}%")
            ->limit(2)
            ->get();

        if ($match->count() === 1) {
            $this->redirect(AccountResource::getUrl('view', ['record' => $match->first()]));
        }
    }
}
