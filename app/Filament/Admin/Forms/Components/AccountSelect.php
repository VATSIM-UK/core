<?php

namespace App\Filament\Admin\Forms\Components;

use App\Filament\Admin\Resources\Accounts\AccountResource;
use Filament\Forms\Components\Select;

class AccountSelect extends Select
{
    protected string $relationshipName = 'account';

    public static function make(?string $relationshipName = null): static
    {
        $relationshipName ??= 'account';

        return parent::make($relationshipName.'_id')
            // Deliberately not AccountResource::getGloballySearchableAttributes():
            // searching by Discord ID would let staff enumerate accounts by an
            // identifier they are not otherwise entitled to look up.
            ->searchable(['id', 'name_first', 'name_last', 'nickname'])
            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
            ->setRelationshipName($relationshipName);
    }

    public function setRelationshipName(string $relationshipName): static
    {
        $this->relationshipName = $relationshipName;

        return $this->setRelationship();
    }

    private function setRelationship(): static
    {
        return $this->resourceRelationship(AccountResource::class, $this->relationshipName, 'first_name');
    }
}
