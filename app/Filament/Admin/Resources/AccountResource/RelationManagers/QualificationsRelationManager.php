<?php

namespace App\Filament\Admin\Resources\AccountResource\RelationManagers;

use App\Enums\QualificationTypeEnum;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms;
use Illuminate\Support\Facades\Auth;
use App\Models\Mship\Qualification;

class QualificationsRelationManager extends RelationManager
{
    protected static string $relationship = 'qualifications';

    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code'),
                Tables\Columns\TextColumn::make('name_long')->label('Name'),
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
                                ->visible(fn () => ! $hasAdminRating),];
                    })
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
