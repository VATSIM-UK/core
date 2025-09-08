<?php

namespace App\Filament\Admin\Resources\PositionGroupResource\RelationManagers;

use App\Models\Mship\Account;
use App\Services\Training\EndorsementCreationService;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
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
                TextColumn::make('account.id')->label('CID')->searchable(),
                TextColumn::make('account.name')->label('Name')->searchable()->sortable(),
                TextColumn::make('created_at')->label('Endorsed')->isoDateTimeFormat('lll'),
            ])
            ->headerActions([
                CreateAction::make()->schema([
                    TextInput::make('account_id')->label('CID')->required(),
                ])->action(function (array $data) {
                    try {
                        $account = Account::findOrFail($data['account_id']);
                    } catch (ModelNotFoundException) {
                        Notification::make()->title('Account not found')->danger()->send();

                        return;
                    }

                    EndorsementCreationService::createPermanent($this->getOwnerRecord(), $account, auth()->user());
                })->visible(fn () => auth()->user()->can('endorse', $this->getOwnerRecord())),
            ]);
    }
}
