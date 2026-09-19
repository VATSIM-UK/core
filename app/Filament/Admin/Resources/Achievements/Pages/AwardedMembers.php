<?php

namespace App\Filament\Admin\Resources\Achievements\Pages;

use App\Filament\Admin\Resources\Achievements\AchievementResource;
use App\Models\Mship\Achievement;
use App\Models\Mship\AchievementAward;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class AwardedMembers extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string $resource = AchievementResource::class;

    protected ?string $heading = '';

    protected string $view = 'filament.pages.achievements.awarded-members';

    public Achievement $record;

    public function mount(Achievement $record): void
    {
        $this->record = $record;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(AchievementAward::query()->where('achievement_id', $this->record->id)->with(['account', 'creator']))
            ->columns([
                TextColumn::make('account.id')
                    ->label('CID')->searchable(),
                TextColumn::make('account.name')
                    ->label('Name')->searchable(['name_first', 'name_last']),
                TextColumn::make('created_at')
                    ->label('Awarded At')
                    ->dateTime(),
                TextColumn::make('creator.name')
                    ->label('Awarded By')->toggleable(isToggledHiddenByDefault: true),
            ])->recordActions([
                Action::make('remove')
                    ->label('Remove')
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->visible(fn (): bool => AchievementResource::canAward())
                    ->authorize(fn (): bool => AchievementResource::canAward())
                    ->requiresConfirmation()
                    ->action(function (AchievementAward $record) {
                        $record->deleted_by = auth()->user()->id;
                        $record->saveQuietly();
                        $record->delete();

                        Notification::make()
                            ->title('Awarded achievement removed successfully.')
                            ->success()
                            ->send();
                    })
                    ->modalHeading(fn (AchievementAward $record): string => "Remove {$this->record->name} from {$record->account->name}?")
                    ->modalSubheading('This action will remove the achievement from the member.'),
            ]);
    }
}
