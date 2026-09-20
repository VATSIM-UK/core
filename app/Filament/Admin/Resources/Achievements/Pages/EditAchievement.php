<?php

namespace App\Filament\Admin\Resources\Achievements\Pages;

use App\Filament\Admin\Resources\Achievements\AchievementResource;
use App\Models\Mship\Achievement;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAchievement extends EditRecord
{
    protected static string $resource = AchievementResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->visible(fn (Achievement $record): bool => AchievementResource::canEdit($record))->before(function () {
                $this->record->deleted_by = auth()->id();
                $this->record->saveQuietly();
            }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
