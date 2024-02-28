<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EndorsementRequestResource\Pages;
use App\Models\Atc\Position;
use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account\EndorsementRequest;
use App\Models\Mship\Qualification;
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

    protected static ?string $navigationGroup = 'Mentoring';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Request details')->columns(2)->schema([
                    Forms\Components\TextInput::make('account_id')->label('CID')->required(),

                    Forms\Components\Select::make('endorsable_type')->options([
                        'App\Models\Atc\PositionGroup' => 'Tier 1 / 2 Endorsements',
                        'App\Models\Atc\Position' => 'Solo Endorsement',
                        'App\Models\Mship\Qualification' => 'Rating Endorsement',
                    ])->required()->live(),

                    Forms\Components\Hidden::make('requested_by')->default(auth()->id()),
                ]),

                Forms\Components\Section::make('Tier 1 Endorsement')->schema([
                    Forms\Components\Select::make('endorsable_id')->label('Tier 1 / 2 Name')->options(function () {
                        return PositionGroup::orderBy('name')->pluck('name', 'id');
                    })->required()->searchable(),
                ])->visible(fn (Get $get): bool => $get('endorsable_type') === 'App\Models\Atc\PositionGroup'),

                Forms\Components\Section::make('Solo Endorsement')->schema([
                    Forms\Components\Select::make('endorsable_id')->label('Endorsement Name')->options(function () {
                        return Position::temporarilyEndorsable()->orderBy('callsign')->pluck('callsign', 'id');
                    })->required()->searchable(),
                ])->visible(fn (Get $get): bool => $get('endorsable_type') === 'App\Models\Atc\Position'),

                Forms\Components\Section::make('Rating Endorsement')->schema([
                    Forms\Components\Select::make('endorsable_id')->label('Rating')->options(function () {
                        return Qualification::ofType('atc')->orderBy('vatsim')->pluck('code', 'id');
                    })->required()->searchable(),
                ])->visible(fn (Get $get): bool => $get('endorsable_type') === 'App\Models\Mship\Qualification'),

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
                Tables\Columns\TextColumn::make('requester.name')->label('Requested By'),
                Tables\Columns\TextColumn::make('created_at')->label('Requested')->isoDateTimeFormat('lll'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->form([
                        Forms\Components\Select::make('type')
                            ->options([
                                'Permanent' => 'Permanent',
                                'Temporary' => 'Temporary',
                            ])
                            ->default('Temporary')
                            ->live()
                            ->required(),

                        Forms\Components\TextInput::make('days')
                            ->label('Valid for (Days)')
                            ->numeric()
                            ->step(1)
                            ->minValue(7)
                            ->placeholder(7)
                            ->maxValue(function (EndorsementRequest $endorsementRequest) {
                                if (! $endorsementRequest->endorsable instanceof Position) {
                                    return 365;
                                }

                                $account = $endorsementRequest->account;
                                $maximumDays = 90;

                                return $maximumDays - $account->daysSpentTemporarilyEndorsedOn($endorsementRequest->endorsable);
                            })
                            ->required(fn (Get $get): bool => $get('type') === 'Temporary')
                            ->visible(fn (Get $get): bool => $get('type') === 'Temporary'),

                        Forms\Components\Textarea::make('notes'),
                    ])
                    ->action(function (EndorsementRequest $endorsementRequest, array $data) {
                        event(new \App\Events\Training\EndorsementRequestApproved($endorsementRequest, $data['days'] ?? null));

                        Notification::make()
                            ->title('Endorsement request approved')
                            ->success();
                    })->visible(fn (EndorsementRequest $endorsementRequest) => $endorsementRequest->status === 'Pending' &&
                            auth()->user()->can('approve', $endorsementRequest)),
                Tables\Actions\Action::make('reject')
                    ->requiresConfirmation()
                    ->action(function (EndorsementRequest $endorsementRequest, array $data) {
                        $endorsementRequest->markRejected();

                        Notification::make()
                            ->title('Endorsement request rejected')
                            ->success();
                    })->visible(fn (EndorsementRequest $endorsementRequest) => $endorsementRequest->status === 'Pending' &&
                            auth()->user()->can('approve', $endorsementRequest)),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEndorsementRequests::route('/'),
            'create' => Pages\CreateEndorsementRequest::route('/create'),
        ];
    }
}
