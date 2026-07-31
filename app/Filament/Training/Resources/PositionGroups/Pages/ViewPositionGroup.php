<?php

namespace App\Filament\Training\Resources\PositionGroups\Pages;

use App\Filament\Training\Pages\Endorsements;
use App\Filament\Training\Resources\PositionGroups\PositionGroupResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewPositionGroup extends ViewRecord
{
    protected static string $resource = PositionGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back to Endorsements')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(Endorsements::urlFor(PositionGroupResource::class)),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
