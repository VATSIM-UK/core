<?php

namespace App\Filament\Training\Resources\TrainingPlaces\Pages;

use App\Filament\Admin\Forms\Components\AccountSelect;
use App\Filament\Training\Pages\TrainingPlace\ViewTrainingPlace;
use App\Filament\Training\Resources\TrainingPlaces\TrainingPlaceResource;
use App\Filament\Training\Resources\TrainingPlaces\Widgets\TrainingPlaceCategoryChart;
use App\Filament\Training\Resources\TrainingPlaces\Widgets\TrainingPlaceOffersOverview;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Services\Training\TrainingPlaceService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

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

                    Select::make('training_position_id')
                        ->label('Training Position')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->options(fn (): array => TrainingPosition::query()
                            ->with('position')
                            ->orderBy('id')
                            ->get()
                            ->mapWithKeys(function (TrainingPosition $trainingPosition): array {
                                $label = $trainingPosition->position?->callsign
                                    ?? collect($trainingPosition->cts_positions)->filter()->first();

                                return [$trainingPosition->id => $label];
                            })
                            ->all()),

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
                    abort_unless($actor->can('createAdhoc', TrainingPlace::class), 403);

                    $student = Account::query()->findOrFail($data['account_id']);
                    $trainingPosition = TrainingPosition::query()->with('position')->findOrFail($data['training_position_id']);

                    $reason = trim((string) $data['reason']);

                    $trainingPlace = app(TrainingPlaceService::class)->createAdhocTrainingPlace(
                        $student,
                        $trainingPosition,
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
}
