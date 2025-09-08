<?php

namespace App\Filament\Admin\Resources;

use App\Enums\BanTypeEnum;
use App\Filament\Admin\Forms\Components\AccountSelect;
use App\Filament\Admin\Resources\BanResource\Pages\ListBans;
use App\Filament\Admin\Resources\BanResource\Pages\ViewBan;
use App\Filament\Admin\Resources\BanResource\RelationManagers\NotesRelationManager;
use App\Models\Mship\Account\Ban;
use App\Models\Mship\Ban\Reason;
use Carbon\CarbonInterval;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class BanResource extends Resource
{
    protected static ?string $model = Ban::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-scale';

    protected static string|\UnitEnum|null $navigationGroup = 'User Management';

    public static function getNavigationBadge(): ?string
    {
        return Cache::remember('admin.bans.local-ban-count', CarbonInterval::minute(5), fn () => static::getModel()::isActive()->isLocal()->count());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('type')->options(BanTypeEnum::class)->disabled(),
            DateTimePicker::make('created_at')->label('Issued')->disabled(),

            AccountSelect::make('account')->label('Subject')->disabled(),
            AccountSelect::make('banner')->label('Banned By')->disabled(),

            Fieldset::make('Reason')->schema([
                Select::make('reason_id')
                    ->label('Category')
                    ->required()
                    ->options(Reason::all()->mapWithKeys(function (Reason $model) {
                        return [$model->getKey() => str($model)];
                    }))->disabled(),

                Placeholder::make('reason.description')->label('Category Description')->content(fn ($record) => $record->reason?->reason_text),

                Textarea::make('reason_extra'),
            ]),

            Fieldset::make('Timings')->schema([
                DateTimePicker::make('period_start')->required()->disabled(),
                DateTimePicker::make('period_finish')->disabled(),
                DateTimePicker::make('repealed_at')->visible(fn ($record) => $record->repealed_at),
            ]),

            DateTimePicker::make('updated_at')->label('Last Update')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('account.name')->label('Subject')->searchable(['name_first', 'name_last', 'id'])->viewResource(AccountResource::class),
                TextColumn::make('banner.name')->label('Banned By')->searchable(['name_first', 'name_last', 'id'])->viewResource(AccountResource::class),
                TextColumn::make('created_at')->label('Issued')->dateTime('d/m/Y')->sortable(),
                TextColumn::make('period_start')->label('Started')->since()->description(fn ($record) => $record->period_start),
                TextColumn::make('period_finish')->label('Ends')->since()->description(fn ($record) => $record->period_finish)->sortable(),
                TextColumn::make('type_string')->label('Type'),
                IconColumn::make('active')->boolean()->getStateUsing(fn ($record) => $record->is_active)->trueColor('danger')->falseColor('success'),
            ])
            ->filters([
                SelectFilter::make('type')->options(BanTypeEnum::class)->default(BanTypeEnum::Local->value),
                TernaryFilter::make('active')
                    ->queries(
                        true: fn (Builder $query) => $query->isActive(),
                        false: fn (Builder $query) => $query->isInActive(),
                    )->default(true),

                TernaryFilter::make('repealed')
                    ->queries(
                        true: fn (Builder $query) => $query->isRepealed(),
                        false: fn (Builder $query) => $query->isNotRepealed(),
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            NotesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBans::route('/'),
            'view' => ViewBan::route('/{record}'),
        ];
    }
}
