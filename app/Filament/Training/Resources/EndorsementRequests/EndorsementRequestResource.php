<?php

namespace App\Filament\Training\Resources\EndorsementRequests;

use App\Events\Training\EndorsementRequestApproved;
use App\Filament\Admin\Forms\Components\AccountSelect;
use App\Filament\Training\Resources\EndorsementRequests\Pages\CreateEndorsementRequest;
use App\Filament\Training\Resources\EndorsementRequests\Pages\ListEndorsementRequests;
use App\Models\Atc\Position;
use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account\EndorsementRequest;
use App\Models\Mship\Qualification;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EndorsementRequestResource extends Resource
{
    protected static ?string $model = EndorsementRequest::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Endorsements';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Request details')->columns(2)->columnSpanFull()->schema([
                    AccountSelect::make()->label('Account')->required()->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->id})"),

                    Select::make('endorsable_type')->options([
                        'App\Models\Atc\PositionGroup' => 'Tier 1 / 2 Endorsements',
                        'App\Models\Atc\Position' => 'Solo Endorsement',
                        'App\Models\Mship\Qualification' => 'Rating Endorsement',
                    ])->required()->live(),

                    Hidden::make('requested_by')->default(auth()->id()),
                ]),

                Section::make('Tier 1 Endorsement')->columnSpanFull()->schema([
                    Select::make('endorsable_id')->label('Tier 1 / 2 Name')->options(function () {
                        return PositionGroup::orderBy('name')->pluck('name', 'id');
                    })->required()->searchable(),
                ])->visible(fn (Get $get): bool => $get('endorsable_type') === 'App\Models\Atc\PositionGroup'),

                Section::make('Solo Endorsement')->columnSpanFull()->schema([
                    Select::make('endorsable_id')->label('Endorsement Name')->options(function () {
                        return Position::temporarilyEndorsable()->orderBy('callsign')->pluck('callsign', 'id');
                    })->required()->searchable(),
                ])->visible(fn (Get $get): bool => $get('endorsable_type') === 'App\Models\Atc\Position'),

                Section::make('Rating Endorsement')->columnSpanFull()->schema([
                    Select::make('endorsable_id')->label('Rating')->options(function () {
                        return Qualification::ofType('atc')->orderBy('vatsim')->pluck('code', 'id');
                    })->required()->searchable(),
                ])->visible(fn (Get $get): bool => $get('endorsable_type') === 'App\Models\Mship\Qualification'),

                Section::make('Additional details')->columnSpanFull()->schema([
                    Textarea::make('notes'),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('account_id')->label('CID'),
                TextColumn::make('account.name')->label('Name'),
                TextColumn::make('typeForHumans')->label('Type'),
                TextColumn::make('endorsable.name')->label('Position/Endorsement'),
                TextColumn::make('status')->badge()->color(fn (EndorsementRequest $endorsementRequest) => match ($endorsementRequest->status) {
                    'Approved' => 'success',
                    'Rejected' => 'danger',
                    default => 'warning',
                }),
                TextColumn::make('requester.name')->label('Requested By'),
                TextColumn::make('created_at')->label('Requested')->isoDateTimeFormat('lll'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('account')
                    ->label('CID')
                    ->multiple()
                    ->relationship(
                        'account',
                        'id',
                        fn (Builder $query) => $query->whereIn(
                            'id',
                            EndorsementRequest::query()->select('account_id'),
                        ),
                    )
                    ->searchable()
                    ->preload(false),
                SelectFilter::make('actioned_type')
                    ->label('Status')
                    ->multiple()
                    ->options([
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->paginated([10, 25, 50, 100])
            ->recordActions([
                Action::make('approve')
                    ->schema(static::approvalSchema())
                    ->modalSubmitActionLabel('Approve')
                    ->action(function (EndorsementRequest $endorsementRequest, array $data) {
                        event(new EndorsementRequestApproved($endorsementRequest, $data['days'] ?? null));

                        Notification::make()
                            ->title('Endorsement request approved')
                            ->success();
                    })->visible(fn (EndorsementRequest $endorsementRequest) => $endorsementRequest->status === 'Pending' &&
                            auth()->user()->can('approve', $endorsementRequest)),
                Action::make('reject')
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
            'index' => ListEndorsementRequests::route('/'),
            'create' => CreateEndorsementRequest::route('/create'),
        ];
    }

    public static function approvalSchema(): array
    {
        return [
            Select::make('type')
                ->options([
                    'Permanent' => 'Permanent',
                    'Temporary' => 'Temporary',
                ])
                ->default('Temporary')
                ->live()
                ->required(),

            TextInput::make('days')
                ->label('Valid for (Days)')
                ->numeric()
                ->step(1)
                ->minValue(function () {
                    return auth()->user()->can('endorsement.bypass.minimumdays')
                        ? null
                        : 7;
                })
                ->placeholder(7)
                ->maxValue(function (?EndorsementRequest $endorsementRequest = null) {
                    if (! $endorsementRequest || ! $endorsementRequest->endorsable instanceof Position) {
                        return 365;
                    }

                    return auth()->user()->can('endorsement.bypass.maximumdays')
                        ? null
                        : 90 - $endorsementRequest->account->daysSpentTemporarilyEndorsedOn($endorsementRequest->endorsable);
                })
                ->required(fn (Get $get): bool => $get('type') === 'Temporary')
                ->visible(fn (Get $get): bool => $get('type') === 'Temporary'),

            Textarea::make('notes'),
        ];
    }
}
