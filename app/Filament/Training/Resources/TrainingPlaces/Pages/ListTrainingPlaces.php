<?php

namespace App\Filament\Training\Resources\TrainingPlaces\Pages;

use App\Enums\QualificationTypeEnum;
use App\Filament\Admin\Forms\Components\AccountSelect;
use App\Filament\Training\Pages\TrainingPlace\ViewTrainingPlace;
use App\Filament\Training\Resources\TrainingPlaces\TrainingPlaceResource;
use App\Filament\Training\Resources\TrainingPlaces\Widgets\TrainingPlaceCategoryChart;
use App\Filament\Training\Resources\TrainingPlaces\Widgets\TrainingPlaceOffersOverview;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use App\Services\Training\TrainingPlaceService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ListTrainingPlaces extends ListRecords
{
    protected static string $resource = TrainingPlaceResource::class;

    protected static ?string $title = 'Training Places';

    protected function getHeaderWidgets(): array
    {
        return [
            TrainingPlaceCategoryChart::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            TrainingPlaceOffersOverview::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createAdhocTrainingPlace')
                ->label('Create Ad-hoc Training Place')
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->visible(function (): bool {
                    /** @var Account|null $user */
                    $user = Auth::user();

                    return (bool) ($user?->can('createAdhoc', TrainingPlace::class));
                })
                ->schema([
                    AccountSelect::make('account')
                        ->label('Student')
                        ->required(),

                    Select::make('trainable')
                        ->label('Training')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->options(fn (): array => $this->adhocTrainableOptions()),

                    Textarea::make('reason')
                        ->label('Reason')
                        ->helperText('This will be saved on the member account to explain why this training place was created outside the usual waiting list flow.')
                        ->rows(4)
                        ->required()
                        ->minLength(10),
                ])
                ->modalHeading('Create Ad-hoc Training Place')
                ->modalDescription('Create a training place for a member without requiring a waiting list record.')
                ->modalSubmitActionLabel('Create Training Place')
                ->action(function (array $data): void {
                    /** @var Account $actor */
                    $actor = Auth::user();
                    abort_unless($actor instanceof Account, 403);

                    $student = Account::query()->findOrFail($data['account_id']);
                    $trainable = $this->resolveAdhocTrainable($data['trainable']);
                    $department = $trainable instanceof Qualification
                        ? WaitingList::PILOT_DEPARTMENT
                        : WaitingList::ATC_DEPARTMENT;

                    abort_unless($actor->can('createAdhoc', [TrainingPlace::class, $department]), 403);

                    $reason = trim((string) $data['reason']);

                    $trainingPlace = app(TrainingPlaceService::class)->createAdhocTrainingPlace(
                        $student,
                        $trainable,
                        $reason,
                        $actor,
                    );

                    Notification::make()
                        ->title('Ad-hoc training place created')
                        ->success()
                        ->actions([
                            Action::make('view')
                                ->label('View Training Place')
                                ->url(ViewTrainingPlace::getUrl(['trainingPlaceId' => $trainingPlace->id]))
                                ->markAsRead(),
                        ])
                        ->send();
                }),
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    protected function adhocTrainableOptions(): array
    {
        /** @var Account|null $user */
        $user = Auth::user();
        $options = [];

        if ($user?->can('createAdhoc', [TrainingPlace::class, WaitingList::ATC_DEPARTMENT])) {
            $options['Training Positions'] = TrainingPosition::query()
                ->with('position')
                ->orderBy('id')
                ->get()
                ->mapWithKeys(function (TrainingPosition $trainingPosition): array {
                    $label = $trainingPosition->position?->callsign
                        ?? collect($trainingPosition->cts_positions)->filter()->first()
                        ?? "Position #{$trainingPosition->id}";

                    return [TrainingPosition::class.'|'.$trainingPosition->id => $label];
                })
                ->all();
        }

        if ($user?->can('createAdhoc', [TrainingPlace::class, WaitingList::PILOT_DEPARTMENT])) {
            $options['Pilot Qualifications'] = Qualification::ofType(QualificationTypeEnum::Pilot->value)
                ->orderBy('vatsim')
                ->get()
                ->mapWithKeys(fn (Qualification $qualification): array => [
                    Qualification::class.'|'.$qualification->id => "{$qualification->name_long} ({$qualification->code})",
                ])
                ->all();
        }

        return $options;
    }

    /**
     * @return TrainingPosition|Qualification
     */
    protected function resolveAdhocTrainable(string $compositeKey): Model
    {
        [$type, $id] = array_pad(explode('|', $compositeKey, 2), 2, null);

        if (! filled($type) || ! filled($id)) {
            throw ValidationException::withMessages([
                'trainable' => 'Invalid training selection.',
            ]);
        }

        $trainable = match ($type) {
            TrainingPosition::class => TrainingPosition::query()->with('position')->find($id),
            Qualification::class => Qualification::query()->find($id),
            default => null,
        };

        if (! $trainable) {
            throw ValidationException::withMessages([
                'trainable' => 'The selected training option could not be found.',
            ]);
        }

        if ($trainable instanceof Qualification && $trainable->type !== QualificationTypeEnum::Pilot->value) {
            throw ValidationException::withMessages([
                'trainable' => 'The selected qualification is not a pilot qualification.',
            ]);
        }

        return $trainable;
    }
}
