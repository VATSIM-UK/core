<?php

namespace App\Filament\Resources\AccountResource\RelationManagers;

use App\Filament\Resources\BanResource;
use App\Models\Mship\Account\Ban;
use App\Models\Mship\Ban\Reason;
use App\Notifications\Mship\BanCreated;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BansRelationManager extends RelationManager
{
    protected static string $relationship = 'bans';

    protected static ?string $recordTitleAttribute = 'id';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('reason')
                ->required()
                ->options(Reason::all()->mapWithKeys(function (Reason $model) {
                    return [$model->getKey() => str($model)];
                })),
            Grid::make(2)->schema([
                Forms\Components\Textarea::make('extra_info')
                    ->required()
                    ->helperText('This is sent to the member')
                    ->minLength(5),
                Forms\Components\Textarea::make('note')
                    ->helperText('This is **not** sent to the member')
                    ->required()
                    ->minLength(5),
            ]),
        ]);
    }

    protected function canCreate(): bool
    {
        return auth()->user()->can('create', [$this->getTable()->getModel(), $this->ownerRecord]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('active')->boolean()->getStateUsing(fn ($record) => $record->is_active)->trueColor('danger')->falseColor('success'),
                Tables\Columns\TextColumn::make('type_string')->label('Type'),
                Tables\Columns\TextColumn::make('reason.name')->label('Reason'),
                Tables\Columns\TextColumn::make('period_amount_string')->label('Duration'),
                Tables\Columns\TextColumn::make('period_start')->label('Started')->since()->description(fn ($record) => $record->period_start),
                Tables\Columns\TextColumn::make('period_finish')->label('Ends')->since()->description(fn ($record) => $record->period_finish),
                Tables\Columns\TextColumn::make('banner.name')->label('By'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->disableCreateAnother()
                    ->using(function (array $data, self $livewire) {
                        return static::createBan($data, $livewire);
                    }),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make('repealed')
                    ->label('Repealed')
                    ->placeholder('Without Repealed')
                    ->trueLabel('With Repealed Records')
                    ->falseLabel('Only Repealed Records')
                    ->queries(true: fn ($query) => $query, false: fn ($query) => $query->isRepealed(), blank: fn ($query) => $query->isNotRepealed()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->resource(BanResource::class),
            ]);
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()->can('viewSensitive', $ownerRecord);
    }

    private static function createBan(array $data, self $livewire): Ban
    {
        $ban = $livewire->ownerRecord->addBan(
            Reason::find($data['reason']),
            $data['extra_info'],
            $data['note'],
            auth()->user()->getKey()
        );

        $livewire->ownerRecord->notify(new BanCreated($ban));

        return $ban;
    }
}
