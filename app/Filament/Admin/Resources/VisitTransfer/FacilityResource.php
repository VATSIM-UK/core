<?php

namespace App\Filament\Admin\Resources\VisitTransfer;

use App\Enums\QualificationTypeEnum;
use App\Filament\Admin\Resources\VisitTransfer\FacilityResource\Pages;
use App\Models\Mship\Qualification;
use App\Models\VisitTransfer\Facility;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FacilityResource extends Resource
{
    protected static ?string $model = Facility::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'Visiting / Transferring';

    public static function canAccess(): bool
    {

        return auth()->user()->can('vt.facility.view.*');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('vt.facility.create');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('vt.facility.update.*');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Facility Details')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label('Facility Name')
                            ->required()
                            ->maxLength(255)
                            ->minLength(5),
                        TextInput::make('public_id')
                            ->label('Manual Join Key')
                            ->disabled()
                            ->afterStateHydrated(fn ($component, $state, $record) => $component->state($record?->public_id ?? 'N/A')
                            )
                            ->helperText('Give this key to applicants so that they can apply to join this facility when hidden from public view')
                            ->suffixAction(
                                Action::make('copy')
                                    ->icon('heroicon-m-clipboard')
                                    ->tooltip('Copy')
                                    ->visible(fn ($record) => filled($record?->public_id))
                                    ->action(fn ($record, $livewire) => $livewire->js('navigator.clipboard.writeText("'.$record?->public_id.'")'))
                            )->visibleOn('edit'),
                    ]),
                    Select::make('training_team')
                        ->label('Training Team')
                        ->required()
                        ->options([
                            'atc' => 'ATC',
                            'pilot' => 'Pilot',
                        ])
                        ->default('atc')
                        ->selectablePlaceholder(false)
                        ->live(),
                    Textarea::make('description')
                        ->label('Description')
                        ->rows(3)
                        ->maxLength(1000)
                        ->minLength(25),
                ]),
                Section::make('Application Settings')->schema([
                    Grid::make(2)->schema([
                        Toggle::make('open')
                            ->label('Open for Applications')
                            ->required(),
                        Toggle::make('public')
                            ->label('Publicly Visible')
                            ->required(),
                        Toggle::make('can_visit')
                            ->label('Visiting Available')
                            ->required(),
                        Toggle::make('can_transfer')
                            ->label('Transfer Available')
                            ->required(),
                        Toggle::make('training_required')
                            ->label('Training Required')
                            ->required()
                            ->reactive(),
                        Select::make('training_spaces')
                            ->label('Number of Training Spaces')
                            ->options([
                                null => 'Infinite',
                                1 => '1 Space',
                                2 => '2 Spaces',
                                3 => '3 Spaces',
                                4 => '4 Spaces',
                                5 => '5 Spaces',
                                10 => '10 Spaces',
                                20 => '20 Spaces',
                                50 => '50 Spaces',
                            ])
                            ->nullable()
                            ->default(null)
                            ->selectablePlaceholder(false)
                            ->visible(fn (callable $get) => $get('training_required')),
                        Toggle::make('stage_statement_enabled')
                            ->label('Statement Required')
                            ->required(),
                        Toggle::make('stage_checks')
                            ->label('Automated Checks')
                            ->helperText('Checks against the 50 hours and 90 days rules.')
                            ->required(),
                        Toggle::make('auto_acceptance')
                            ->label('Auto Acceptance')
                            ->helperText('Automatically accept all applicants.')
                            ->required(),

                        Grid::make(2)->reactive()->visible(fn ($get) => $get('training_team') === 'atc')->schema([
                            Select::make('minimum_atc_qualification_id')
                                ->label('Minimum ATC Qualification')
                                ->options(
                                    Qualification::ofType(QualificationTypeEnum::ATC->value)
                                        ->orderBy('vatsim')
                                        ->get()
                                        ->pluck('name', 'id')
                                        ->toArray()
                                )
                                ->placeholder('No Minimum')
                                ->nullable()
                                ->default(null)
                                ->reactive(),

                            Select::make('maximum_atc_qualification_id')
                                ->label('Maximum ATC Qualification')
                                ->options(
                                    Qualification::ofType(QualificationTypeEnum::ATC->value)
                                        ->orderBy('vatsim')
                                        ->get()
                                        ->pluck('name', 'id')
                                        ->toArray()
                                )
                                ->placeholder('No Maximum')
                                ->nullable()
                                ->default(null)
                                ->rules([
                                    fn (callable $get) => function (string $attribute, $value, $fail) use ($get) {
                                        $minId = $get('minimum_atc_qualification_id');

                                        if (! $minId || ! $value) {
                                            return;
                                        }

                                        $minQual = Qualification::find($minId);
                                        $maxQual = Qualification::find($value);

                                        if ($maxQual && $minQual && $maxQual->vatsim < $minQual->vatsim) {
                                            $fail('The Maximum qualification cannot be lower than the Minimum.');
                                        }
                                    },
                                ]),
                        ]),

                        Grid::make(2)->reactive()->visible(fn ($get) => $get('training_team') === 'pilot')->schema([
                            Select::make('minimum_pilot_qualification_id')
                                ->label('Minimum Pilot Qualification')
                                ->options(
                                    Qualification::ofType(QualificationTypeEnum::Pilot->value)
                                        ->orderBy('vatsim')
                                        ->get()
                                        ->pluck('name', 'id')
                                        ->toArray()
                                )
                                ->placeholder('No Minimum')
                                ->nullable()
                                ->default(null)
                                ->reactive(),

                            Select::make('maximum_pilot_qualification_id')
                                ->label('Maximum Pilot Qualification')
                                ->options(
                                    Qualification::ofType(QualificationTypeEnum::Pilot->value)
                                        ->orderBy('vatsim')
                                        ->get()
                                        ->pluck('name', 'id')
                                        ->toArray()
                                )
                                ->placeholder('No Maximum')
                                ->nullable()
                                ->default(null)
                                ->rules([
                                    fn (callable $get) => function (string $attribute, $value, $fail) use ($get) {
                                        $minId = $get('minimum_pilot_qualification_id');

                                        if (! $minId || ! $value) {
                                            return;
                                        }

                                        $minQual = Qualification::find($minId);
                                        $maxQual = Qualification::find($value);

                                        if ($maxQual && $minQual && $maxQual->vatsim < $minQual->vatsim) {
                                            $fail('The Maximum qualification cannot be lower than the Minimum.');
                                        }
                                    },
                                ]),
                        ]),
                    ]),
                ]),
                Section::make('Notification Emails')
                    ->description(fn ($record) => "These email addresses will be sent an email once an application to this facility is succesful. If no email addresses are entered, this will default to {$record?->training_team}-team@vatsim.uk")
                    ->schema([
                        Repeater::make('acceptance_emails')
                            ->label('Notification Emails')
                            ->relationship('emails')
                            ->schema([
                                TextInput::make('email')
                                    ->label('Email Address')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->minItems(0)
                            ->disableLabel(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID'),
                TextColumn::make('name')->label('Facility Name'),
                BadgeColumn::make('type')->label('Type')->getStateUsing(function ($record) {
                    if ($record->can_visit && $record->can_transfer) {
                        return 'Visit & Transfer';
                    } elseif ($record->can_visit) {
                        return 'Visit Only';
                    } elseif ($record->can_transfer) {
                        return 'Transfer Only';
                    } else {
                        return 'N/A';
                    }
                })->colors([
                    'warning' => 'Visit & Transfer',
                    'success' => 'Transfer Only',
                    'primary' => 'Visit Only',
                    'danger' => 'N/A',
                ])->alignCenter(),
                TextColumn::make('training_team')->label('Team')->formatStateUsing(fn ($state) => strtoupper($state))->alignCenter(),
                BadgeColumn::make('stage_statement_enabled')->label('Statement')->getStateUsing(fn ($record) => $record->stage_statement_enabled ? 'Required' : 'Disabled')->colors([
                    'success' => 'Required',
                    'danger' => 'Disabled',
                ]),
                BadgeColumn::make('stage_checks')->label('Checks')->getStateUsing(fn ($record) => $record->stage_checks ? 'Auto' : 'Manual')->colors([
                    'success' => 'Auto',
                    'danger' => 'Manual',
                ]),
                BadgeColumn::make('auto_acceptance')->label('Auto Accept')->getStateUsing(fn ($record) => $record->auto_acceptance ? 'Enabled' : 'Disabled')->colors([
                    'success' => 'Enabled',
                    'danger' => 'Disabled',
                ]),
                BadgeColumn::make('open')->label('Open')->getStateUsing(fn ($record) => $record->open ? 'Yes' : 'No')->colors([
                    'success' => 'Yes',
                    'danger' => 'No',
                ]),
                BadgeColumn::make('public')->label('Visibility')->getStateUsing(fn ($record) => $record->public ? 'Public' : 'Private')->colors([
                    'success' => 'Public',
                    'danger' => 'Private',
                ]),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListFacilities::route('/'),
            'create' => Pages\CreateFacility::route('/create'),
            'edit' => Pages\EditFacility::route('/{record}/edit'),
        ];
    }
}
