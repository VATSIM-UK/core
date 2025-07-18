<?php

namespace App\Filament\Resources\TheoryManagementResource\Pages;

use App\Filament\Resources\TheoryManagementResource;
use App\Models\Cts\TheoryManagement;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListTheoryManagement extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = TheoryManagementResource::class;

    protected static string $view = 'filament.pages.training.theory-management';

    protected const GENERAL_ITEMS = [
        'theory_questions' => 'Number of Questions',
        'theory_minutes' => 'Time Allowed (minutes)',
        'theory_passmark' => 'Passmark',
    ];

    public function table(): Table
    {
        return Table::make($this)
            ->query(TheoryManagement::query())
            ->columns([
                TextColumn::make('category')
                    ->label('Category')
                    ->getStateUsing(fn ($record) => $this->getCategory($record->item)),

                TextColumn::make('item')
                    ->label('Setting / Exam')
                    ->formatStateUsing(fn ($state) => $this->formatItemLabel($state)),

                TextColumn::make('setting')
                    ->label('Value / Status')
                    ->formatStateUsing(function ($state, $record) {
                        return array_key_exists($record->item, self::GENERAL_ITEMS) ? $state : ($state ? 'Enabled' : 'Disabled');
                    })
                    ->badge()
                    ->color(function ($state, $record) {
                        return array_key_exists($record->item, self::GENERAL_ITEMS) ? null : ($state ? 'success' : 'danger');
                    }),
            ])
            ->filters([
                SelectFilter::make('Type')
                    ->options([
                        'settings' => 'Settings',
                        'exams' => 'Exams',
                    ])
                    ->default('exams')
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'settings' => $query->whereIn('item', array_keys(self::GENERAL_ITEMS)),
                            'exams' => $query->whereNotIn('item', array_keys(self::GENERAL_ITEMS)),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                // Show Edit only for general items
                EditAction::make()
                    ->visible(fn ($record) => $this->isGeneralItem($record->item) && auth()->user()->can('theory-exams.settings.edit.*'))
                    ->form([
                        TextInput::make('setting')
                            ->label('Value')
                            ->numeric()
                            ->required(),
                    ])
                    ->modalHeading(fn ($record) => $this->formatItemLabel($record->item))
                    ->modalButton('Save')
                    ->successNotificationTitle('Setting updated'),

                // Show "Questions" and enable/disable button for everything else

                Action::make('toggleStatus')
                    ->label(fn ($record) => $record->setting ? 'Disable' : 'Enable')
                    ->icon(fn ($record) => $record->setting ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn ($record) => $record->setting ? 'danger' : 'success')
                    ->visible(fn ($record) => ! $this->isGeneralItem($record->item) && auth()->user()->can('theory-exams.settings.edit.*'))
                    ->action(function ($record) {
                        $record->setting = ! $record->setting;
                        $record->save();

                        return Notification::make()
                            ->title($record->setting ? 'Enabled' : 'Disabled')
                            ->icon($record->setting ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                            ->{ $record->setting ? 'success' : 'danger'}()
                            ->send();
                    })
                    ->requiresConfirmation(),

                EditAction::make('questions')
                    ->label('Questions')
                    ->visible(function ($record) {
                        if ($this->isGeneralItem($record->item)) {
                            return false;
                        }
                        $category = strtolower($this->getCategory($record->item));
                        $permission = "theory-exams.questions.view.$category";

                        return auth()->user()->can($permission);
                    })
                    ->url(fn ($record) => route('filament.app.resources.theory-managements.edit', ['record' => $record->id])),
            ]);
    }

    protected function isGeneralItem(string $item): bool
    {
        return array_key_exists($item, self::GENERAL_ITEMS);
    }

    protected function formatItemLabel(string $item): string
    {
        return self::GENERAL_ITEMS[$item] ?? strtoupper(str_replace('theory_', '', $item));
    }

    protected function getCategory(string $item): string
    {
        if ($this->isGeneralItem($item)) {
            return 'Settings';
        }

        $item = strtoupper(str_replace('theory_', '', $item));

        return match (true) {
            str_starts_with($item, 'S') || str_starts_with($item, 'C') => 'ATC',
            str_starts_with($item, 'P') => 'Pilot',
            default => 'Other',
        };
    }
}
