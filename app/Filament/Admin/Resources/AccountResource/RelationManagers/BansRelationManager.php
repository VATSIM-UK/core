<?php

namespace App\Filament\Admin\Resources\AccountResource\RelationManagers;

use App\Filament\Admin\Helpers\Pages\LogRelationAccess;
use App\Filament\Admin\Resources\BanResource;
use App\Models\Mship\Account\Ban;
use App\Models\Mship\Ban\Reason;
use App\Notifications\Mship\BanCreated;
use Filament\Actions\CreateAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BansRelationManager extends RelationManager
{
    use LogRelationAccess;

    protected static string $relationship = 'bans';

    protected static ?string $recordTitleAttribute = 'id';

    protected function getLogActionName(): string
    {
        return 'ViewBans';
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('reason')
                ->required()
                ->options(Reason::all()->mapWithKeys(function (Reason $model) {
                    return [$model->getKey() => str($model)];
                })),
            Grid::make(2)->schema([
                Textarea::make('extra_info')
                    ->required()
                    ->helperText('This is sent to the member')
                    ->minLength(5),
                Textarea::make('note')
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
                IconColumn::make('active')->boolean()->getStateUsing(fn ($record) => $record->is_active)->trueColor('danger')->falseColor('success'),
                TextColumn::make('type_string')->label('Type'),
                TextColumn::make('reason.name')->label('Reason'),
                TextColumn::make('period_amount_string')->label('Duration'),
                TextColumn::make('period_start')->label('Started')->since()->description(fn ($record) => $record->period_start),
                TextColumn::make('period_finish')->label('Ends')->since()->description(fn ($record) => $record->period_finish),
                TextColumn::make('banner.name')->label('By'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->disableCreateAnother()
                    ->using(function (array $data, self $livewire) {
                        return static::createBan($data, $livewire);
                    }),
            ])
            ->filters([
                TrashedFilter::make('repealed')
                    ->label('Repealed')
                    ->placeholder('Without Repealed')
                    ->trueLabel('With Repealed Records')
                    ->falseLabel('Only Repealed Records')
                    ->queries(true: fn ($query) => $query, false: fn ($query) => $query->isRepealed(), blank: fn ($query) => $query->isNotRepealed()),
            ])
            ->recordActions([
                ViewAction::make()->resource(BanResource::class),
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
