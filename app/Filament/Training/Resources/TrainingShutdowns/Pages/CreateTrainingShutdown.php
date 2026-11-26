<?php

namespace App\Filament\Training\Resources\TrainingShutdowns\Pages;

use App\Filament\Training\Resources\TrainingShutdowns\TrainingShutdownResource;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;

class CreateTrainingShutdown extends CreateRecord
{
    protected static string $resource = TrainingShutdownResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        abort_unless(TrainingShutdownResource::canManageDepartment($data['department']), 403);

        $range = $data['date_range'];

        $data['begins_at'] = Carbon::parse($range['start'])->startOfDay();
        $data['ends_at'] = Carbon::parse($range['end'])->endOfDay();

        unset($data['date_range']);

        return $data;
    }
}
