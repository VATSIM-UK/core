<?php

namespace App\Filament\Admin\Forms\Components;

use App\Filament\Admin\Resources\AccountResource;
use Filament\Forms\Components\Select;

class AccountSelect extends Select
{
    protected string $relationshipName = 'account';

    public static function make(string $relationshipName = 'account'): static
    {
        return parent::make($relationshipName.'_id')
            ->searchable(AccountResource::getGloballySearchableAttributes())
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
