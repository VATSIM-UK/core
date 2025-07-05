<?php

namespace App\Filament\Admin\Resources;

use App\Enums\BanTypeEnum;
use App\Filament\Admin\Forms\Components\AccountSelect;
use App\Filament\Admin\Resources\BanResource\RelationManagers\NotesRelationManager;
use App\Filament\Resources\BanResource\Pages;
use App\Models\Mship\Account\Ban;
use App\Models\Mship\Ban\Reason;
use Carbon\CarbonInterval;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class BanResource extends Resource
{
    protected static ?string $model = Ban::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationGroup = 'User Management';

    public static function getNavigationBadge(): ?string
    {
        return Cache::remember('admin.bans.local-ban-count', CarbonInterval::minute(5), fn () => static::getModel()::isActive()->isLocal()->count());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('type')->options(BanTypeEnum::class)->disabled(),
            Forms\Components\DateTimePicker::make('created_at')->label('Issued')->disabled(),

            AccountSelect::make('account')->label('Subject')->disabled(),
            AccountSelect::make('banner')->label('Banned By')->disabled(),

            Forms\Components\Fieldset::make('Reason')->schema([
                Forms\Components\Select::make('reason_id')
                    ->label('Category')
                    ->required()
                    ->options(Reason::all()->mapWithKeys(function (Reason $model) {
                        return [$model->getKey() => str($model)];
                    }))->disabled(),

                Forms\Components\Placeholder::make('reason.description')->label('Category Description')->content(fn ($record) => $record->reason?->reason_text),

                Forms\Components\Textarea::make('reason_extra'),
            ]),

            Forms\Components\Fieldset::make('Timings')->schema([
                Forms\Components\DateTimePicker::make('period_start')->required()->disabled(),
                Forms\Components\DateTimePicker::make('period_finish')->disabled(),
                Forms\Components\DateTimePicker::make('repealed_at')->visible(fn ($record) => $record->repealed_at),
            ]),

            Forms\Components\DateTimePicker::make('updated_at')->label('Last Update')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('account.name')->label('Subject')->searchable(['name_first', 'name_last', 'id'])->viewResource(AccountResource::class),
                Tables\Columns\TextColumn::make('banner.name')->label('Banned By')->searchable(['name_first', 'name_last', 'id'])->viewResource(AccountResource::class),
                Tables\Columns\TextColumn::make('created_at')->label('Issued')->dateTime('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('period_start')->label('Started')->since()->description(fn ($record) => $record->period_start),
                Tables\Columns\TextColumn::make('period_finish')->label('Ends')->since()->description(fn ($record) => $record->period_finish)->sortable(),
                Tables\Columns\TextColumn::make('type_string')->label('Type'),
                Tables\Columns\IconColumn::make('active')->boolean()->getStateUsing(fn ($record) => $record->is_active)->trueColor('danger')->falseColor('success'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->options(BanTypeEnum::class)->default(BanTypeEnum::Local->value),
                Tables\Filters\TernaryFilter::make('active')
                    ->queries(
                        true: fn (Builder $query) => $query->isActive(),
                        false: fn (Builder $query) => $query->isInActive(),
                    )->default(true),

                Tables\Filters\TernaryFilter::make('repealed')
                    ->queries(
                        true: fn (Builder $query) => $query->isRepealed(),
                        false: fn (Builder $query) => $query->isNotRepealed(),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => \App\Filament\Admin\Resources\BanResource\Pages\ListBans::route('/'),
            'view' => \App\Filament\Admin\Resources\BanResource\Pages\ViewBan::route('/{record}'),
        ];
    }
}
