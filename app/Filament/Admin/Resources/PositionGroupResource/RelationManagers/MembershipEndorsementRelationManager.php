<?php

namespace App\Filament\Admin\Resources\PositionGroupResource\RelationManagers;

use App\Services\Training\EndorsementService;
use App\Services\Training\TrainingSuccessesAnnouncementService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MembershipEndorsementRelationManager extends RelationManager
{
    protected static string $relationship = 'membershipEndorsement';

    protected static ?string $title = 'Endorsed Members';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('account.id')->label('CID')->searchable(),
                Tables\Columns\TextColumn::make('account.name')->label('Name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Endorsed')->isoDateTimeFormat('lll'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->form([
                    Forms\Components\TextInput::make('account_id')->label('CID')->required(),
                ])->action(function (array $data) {
                    try {
                        $account = \App\Models\Mship\Account::findOrFail($data['account_id']);
                    } catch (ModelNotFoundException) {
                        Notification::make()->title('Account not found')->danger()->send();

                        return;
                    }

                    $positionGroup = $this->getOwnerRecord();
                    EndorsementService::createPermanent($positionGroup, $account, auth()->user());

                    app(TrainingSuccessesAnnouncementService::class)->announceTierEndorsement($account, $positionGroup);
                })->visible(fn () => auth()->user()->can('endorse', $this->getOwnerRecord())),
            ]);
    }
}
