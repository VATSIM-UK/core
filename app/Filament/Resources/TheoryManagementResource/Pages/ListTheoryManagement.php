<?php

namespace App\Filament\Resources\TheoryManagementResource\Pages;

use App\Filament\Resources\TheoryManagementResource;
use App\Models\Cts\TheoryManagement;
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

    public function table(): Table
    {
        return Table::make($this)
            ->query(TheoryManagement::query())
            ->columns([
                TextColumn::make('item')
                    ->label('Setting')
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'theory_questions' => 'Number of Questions',
                            'theory_minutes' => 'Time Allowed (minutes)',
                            'theory_passmark' => 'Passmark',
                            default => strtoupper(str_replace('theory_', '', $state)),
                        };
                    }),

                TextColumn::make('setting')
                    ->label('Value')
                    ->formatStateUsing(function ($state, $record) {
                        $generalItems = ['theory_questions', 'theory_passmark', 'theory_minutes'];

                        if (in_array($record->item, $generalItems)) {
                            return $state; // Show value with no changes for general items
                        }

                        return $state == 1 ? 'Enabled' : 'Disabled';
                    })
                    ->badge()
                    ->color(function ($state, $record) {
                        $generalItems = ['theory_questions', 'theory_passmark', 'theory_minutes'];

                        if (in_array($record->item, $generalItems)) {
                            return null; // No badge color for general items
                        }

                        return $state == 1 ? 'success' : 'danger';
                    }),
            ])
            ->filters([
                SelectFilter::make('Type')
                    ->options([
                        'general' => 'General',
                        'other' => 'Other',
                    ])
                    ->default('other')
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query; // No filter selected
                        }

                        $generalItems = ['theory_questions', 'theory_minutes', 'theory_passmark'];

                        if ($data['value'] === 'general') {
                            return $query->whereIn('item', $generalItems);
                        } elseif ($data['value'] === 'other') {
                            return $query->whereNotIn('item', $generalItems);
                        }

                        return $query;
                    }),
            ])
            ->actions([
                // Show Edit only for general items
                EditAction::make()
                    ->visible(fn ($record) => in_array($record->item, ['theory_questions', 'theory_passmark', 'theory_minutes']))
                    ->form([
                        \Filament\Forms\Components\TextInput::make('setting')
                            ->label('Value')
                            ->numeric()
                            ->required(),
                    ])
                    ->modalHeading(function ($record) {
                        return match ($record->item) {
                            'theory_questions' => 'Edit Number of Questions',
                            'theory_minutes' => 'Edit Time Allowed (minutes)',
                            'theory_passmark' => 'Edit Passmark',
                            default => 'Edit Setting',
                        };
                    })
                    ->modalButton('Save')
                    ->successNotificationTitle('Setting updated'),

                // Show "Questions" and enable/disable button for everything else

                Action::make('toggleStatus')
                    ->label(fn ($record) => $record->setting ? 'Disable' : 'Enable')
                    ->icon(fn ($record) => $record->setting ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn ($record) => $record->setting ? 'danger' : 'success')
                    ->visible(fn ($record) => ! in_array($record->item, ['theory_questions', 'theory_passmark', 'theory_minutes']))
                    ->action(function ($record) {
                        $record->setting = $record->setting ? 0 : 1;
                        $record->save();

                        return Notification::make()
                            ->title('Status Changed')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),

                EditAction::make('questions')
                    ->label('Questions')
                    ->visible(fn ($record) => ! in_array($record->item, ['theory_questions', 'theory_passmark', 'theory_minutes']))
                    ->url(fn ($record) => route('filament.app.resources.theory-managements.edit', [
                        'record' => $record->id,
                    ])),

            ]);

    }
}
