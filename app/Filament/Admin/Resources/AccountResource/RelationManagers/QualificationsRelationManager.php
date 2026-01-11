<?php

namespace App\Filament\Admin\Resources\AccountResource\RelationManagers;

use App\Enums\QualificationTypeEnum;
use App\Models\Mship\Qualification;
use Carbon\CarbonImmutable;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
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
                Tables\Columns\TextColumn::make('code'),
                Tables\Columns\TextColumn::make('name_long')->label( 'Name'),
                Tables\Columns\TextColumn::make('created_at')->since()->description(fn ($record) => $record->created_at)->label('Awarded')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->options(collect(QualificationTypeEnum::cases())->mapWithKeys(fn ($enum) => [$enum->value => $enum->name]))->multiple(),
            ])->defaultSort('created_at')
            ->headerActions([
                Tables\Actions\Action::make('manual_atc_rating_upgrade')
                    ->label('Manual ATC rating upgrade')
                    ->visible(fn () => Auth::user()->can('account.qualification.manual-upgrade.atc'))
                    ->modalHeading('Manual ATC rating upgrade')
                    ->modalDescription('This only updates the member’s rating within VATSIM UK systems and does not sync to VATSIM.net.')
                    ->form(function (): array {
                        $account = $this->getOwnerRecord();

                        $nextRating = $this->getNextAtcQualification($account);
                        $hasAdminRating = $this->accountHasAdministrativeRating($account);

                        return [
                            Forms\Components\Placeholder::make('warning_admin_rating')
                                ->label('')
                                ->content('Warning: This member does not currently have an administrative rating.')
                                ->visible(fn () => ! $hasAdminRating),

                            Forms\Components\TextInput::make('next_qualification')
                                ->label('Next rating')
                                ->disabled()
                                ->dehydrated(false)
                                ->default(fn () => $nextRating->name_long ?? 'No ATC rating upgrade is available. This account already holds the highest ATC rating.'),

                            Forms\Components\DatePicker::make('awarded_on')
                                ->label('Awarded date')
                                ->default(now()->toDateString())
                                ->maxDate(now())
                                ->required(),
                        ];
                    })
                    ->action(function (array $data): void {
                        $account = $this->getOwnerRecord();

                        $qualificationId = $this->getNextAtcQualification($account)->id ?? null;

                        if (! $qualificationId) {
                            Notification::make()
                                ->title('No rating to assign')
                                ->body('This account already holds the highest ATC rating available.')
                                ->warning()
                                ->send();

                            return;
                        }

                        $qualification = Qualification::query()->findOrFail($qualificationId);

                        $awardedOn = CarbonImmutable::parse($data['awarded_on'])->startOfDay();
                        $account->addQualification($qualification);
                        $account->qualifications()->updateExistingPivot($qualification->getKey(), [
                            'created_at' => $awardedOn,
                            'updated_at' => $awardedOn,
                        ]);

                        $account->addNote('training', sprintf( 'Manual ATC rating upgrade processed in VATSIM UK systems: assigned %s with awarded date %s.', $qualification->name_long, $awardedOn->toDateString(),),Auth::id(),);

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
