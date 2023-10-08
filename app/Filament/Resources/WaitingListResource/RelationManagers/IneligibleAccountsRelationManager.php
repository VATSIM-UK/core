<?php

namespace App\Filament\Resources\WaitingListResource\RelationManagers;

class IneligibleAccountsRelationManager extends AccountsRelationManager
{
    protected static string $relationship = 'ineligibleAccounts';
}
