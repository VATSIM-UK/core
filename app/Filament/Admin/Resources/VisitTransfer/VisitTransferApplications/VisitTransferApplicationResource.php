<?php

namespace App\Filament\Admin\Resources\VisitTransfer\VisitTransferApplications;

use App\Filament\Admin\Resources\VisitTransfer\VisitTransferApplications\Pages\ListVisitTransferApplications;
use App\Filament\Admin\Resources\VisitTransfer\VisitTransferApplications\Pages\ViewVisitTransferApplication;
use App\Models\VisitTransfer\Application;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VisitTransferApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-right-end-on-rectangle';

    protected static string|\UnitEnum|null $navigationGroup = 'Visiting / Transferring';

    protected static ?string $label = 'Applications';

    public static function canAccess(): bool
    {

        return auth()->user()->can('vt.application.view.*');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function table(Table $table): Table
    {

        return $table
            ->columns([
                TextColumn::make('public_id')->label('Public ID')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('account_id')->label('CID')->searchable(),
                TextColumn::make('account.name')->label('Name')->searchable(['name_first', 'name_last']),
                TextColumn::make('facility.name')->label('Facility Name'),
                TextColumn::make('status')->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state, $record) => $record->status_string)
                    ->color(fn ($record) => $record->status_color),
                TextColumn::make('created_at')->label('Created At')->dateTime()->sortable(),
                TextColumn::make('updated_at')->label('Updated At')->dateTime()->sortable(),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        Application::STATUS_IN_PROGRESS => 'In Progress',
                        Application::STATUS_SUBMITTED => 'Submitted',
                        Application::STATUS_UNDER_REVIEW => 'Under Review',
                        Application::STATUS_ACCEPTED => 'Accepted',
                        Application::STATUS_COMPLETED => 'Completed',
                        Application::STATUS_CANCELLED => 'Cancelled',
                        Application::STATUS_REJECTED => 'Rejected',
                    ])->searchable()->preload()->multiple(),
                SelectFilter::make('facility_id')
                    ->label('Facility')
                    ->relationship('facility', 'name', function ($query, $livewire) {
                        $type = $livewire->type;

                        return $query
                            ->when($type === Application::TYPE_TRANSFER, function ($query) {
                                $query->where('can_transfer', true);
                            }
                            )->when($type === Application::TYPE_VISIT, function ($query) {
                                $query->where('can_visit', true);
                            });
                    })
                    ->searchable()
                    ->preload(),

                Filter::make('updated_at')
                    ->schema([
                        DatePicker::make('from')
                            ->label('Updated from'),
                        DatePicker::make('until')
                            ->label('Updated until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $q) => $q->whereDate('updated_at', '>=', $data['from']))
                            ->when($data['until'], fn (Builder $q) => $q->whereDate('updated_at', '<=', $data['until']));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators[] = Indicator::make('updated from '.date('d/m/Y', strtotime($data['from'])))
                                ->removeField('from');
                        }

                        if ($data['until'] ?? null) {
                            $indicators[] = Indicator::make('updated until '.date('d/m/Y', strtotime($data['until'])))
                                ->removeField('until');
                        }

                        return $indicators;
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ])->paginationPageOptions([10, 25, 50]);
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
            'index' => ListVisitTransferApplications::route('/'),
            'view' => ViewVisitTransferApplication::route('/{record}'),
        ];
    }
}
