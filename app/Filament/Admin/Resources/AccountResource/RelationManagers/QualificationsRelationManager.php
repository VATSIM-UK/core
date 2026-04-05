<?php

namespace App\Filament\Admin\Resources\AccountResource\RelationManagers;

use App\Enums\QualificationTypeEnum;
use App\Models\Mship\Qualification;
use App\Services\Training\ManualAtcUpgradeService;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class QualificationsRelationManager extends RelationManager
{
    protected static string $relationship = 'qualifications';

    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code'),
                TextColumn::make('name_long')->label('Name'),
                TextColumn::make('created_at')->since()->description(fn ($record) => $record->created_at)->label('Awarded')->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')->options(collect(QualificationTypeEnum::cases())->mapWithKeys(fn ($enum) => [$enum->value => $enum->name]))->multiple(),
            ])->defaultSort('created_at')
            ->headerActions([
                Action::make('manual_atc_rating_upgrade')
                    ->label('Manual ATC rating upgrade')
                    ->visible(fn () => Auth::user()->can('account.qualification.manual-upgrade.atc'))
                    ->modalHeading('Manual ATC rating upgrade')
                    ->modalDescription('This only updates the member’s rating within VATSIM UK systems and does not sync to VATSIM.net.')
                    ->schema(function (): array {
                        $account = $this->getOwnerRecord();

                        $nextRating = $this->getNextAtcQualification($account);
                        $hasAdminRating = $this->accountHasAdministrativeRating($account);

                        return [
                            Placeholder::make('warning_admin_rating')
                                ->label('')
                                ->content('Warning: This member does not currently have an administrative rating.')
                                ->visible(fn () => ! $hasAdminRating),

                            TextInput::make('next_qualification')
                                ->label('Next rating')
                                ->disabled()
                                ->dehydrated(false)
                                ->default(fn () => $nextRating->name_long ?? 'No ATC rating upgrade is available. This account already holds the highest ATC rating.'),

                            DatePicker::make('awarded_on')
                                ->label('Awarded date')
                                ->default(now()->toDateString())
                                ->maxDate(now())
                                ->required(),
                        ];
                    })
                    ->action(function (array $data): void {
                        $account = $this->getOwnerRecord();
                        $awardedOn = CarbonImmutable::parse($data['awarded_on'])->startOfDay();

                        $qualification = ManualAtcUpgradeService::awardNextAtcQualification($account, $awardedOn, Auth::id());

                        if (! $qualification) {
                            Notification::make()
                                ->title('No rating to assign')
                                ->body('This account already holds the highest ATC rating available.')
                                ->warning()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('ATC rating upgrade processed')
                            ->body("Assigned {$qualification->name_long}")
                            ->success()
                            ->send();
                    }),
            ]);
    }

    protected function getNextAtcQualification($account): ?Qualification
    {
        $currentMax = $account->qualifications()
            ->where('type', 'atc')
            ->max('vatsim');

        return Qualification::query()
            ->where('type', 'atc')
            ->where('vatsim', '>', $currentMax ?? 0)
            ->orderBy('vatsim')
            ->first();
    }

    protected function accountHasAdministrativeRating($account): bool
    {
        return $account->qualifications()
            ->whereIn('type', ['training_atc', 'admin'])
            ->exists();
    }
}
