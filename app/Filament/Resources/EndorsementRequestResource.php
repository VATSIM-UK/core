<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EndorsementRequestResource\Pages;
use App\Models\Mship\Account\EndorsementRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EndorsementRequestResource extends Resource
{
    protected static ?string $model = EndorsementRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Request details')->columns(2)->schema([
                    Forms\Components\TextInput::make('account_id')->label('CID')->required(),

                    Forms\Components\Select::make('endorsable_type')->options([
                        'App\Models\Atc\PositionGroup' => 'Endorsement',
                        'App\Models\Atc\Position' => 'Position',
                    ])->required()->live(),

                    Forms\Components\Hidden::make('requested_by')->default(auth()->id()),
                ]),

                Forms\Components\Section::make('Endorsement')->schema([
                    Forms\Components\Select::make('endorsable_id')->label('Endorsement')->options(function () {
                        return \App\Models\Atc\PositionGroup::all()->pluck('name', 'id');
                    })->required(),
                ])->visible(fn (Get $get): bool => $get('endorsable_type') === 'App\Models\Atc\PositionGroup'),

                Forms\Components\Section::make('Temporary Endorsement')->schema([
                    Forms\Components\Select::make('endorsable_id')->label('Position')->options(function () {
                        return \App\Models\Atc\Position::temporarilyEndorsable()->pluck('callsign', 'id');
                    }),
                    Forms\Components\DatePicker::make('endorsable_expired_at')->required(),
                ])->visible(fn (Get $get): bool => $get('endorsable_type') === 'App\Models\Atc\Position'),

                Forms\Components\Section::make('Additional details')->schema([
                    Forms\Components\Textarea::make('notes'),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('account_id')->label('CID')->searchable(),
                Tables\Columns\TextColumn::make('account.name')->label('Name')->searchable(),
                Tables\Columns\TextColumn::make('typeForHumans')->label('Type'),
                Tables\Columns\TextColumn::make('endorsable.name')->label('Position/Endorsement'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (EndorsementRequest $endorsementRequest) => match ($endorsementRequest->status) {
                    'Approved' => 'success',
                    'Rejected' => 'danger',
                    default => 'warning',
                }),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->requiresConfirmation()
                    ->action(function (EndorsementRequest $endorsementRequest) {
                        $endorsementRequest->markApproved();

                        event(new \App\Events\Training\EndorsementRequestApproved($endorsementRequest));

                        Notification::make()
                            ->title('Endorsement request approved')
                            ->success();
                    })->visible(fn (EndorsementRequest $endorsementRequest) => $endorsementRequest->status === 'Pending'),
            ])
            ->filters([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEndorsementRequests::route('/'),
            'create' => Pages\CreateEndorsementRequest::route('/create'),
        ];
    }
}
