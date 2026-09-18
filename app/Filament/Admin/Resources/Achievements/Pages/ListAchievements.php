<?php

namespace App\Filament\Admin\Resources\Achievements\Pages;

use App\Filament\Admin\Forms\Components\AccountSelect;
use App\Filament\Admin\Resources\Achievements\AchievementResource;
use App\Models\Mship\Achievement;
use App\Models\Mship\AchievementAward;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\HtmlString;

class ListAchievements extends ListRecords
{
    protected static string $resource = AchievementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->visible(fn (): bool => AchievementResource::canCreate()),

            Action::make('award')
                ->label('Award Achievement')
                ->color('success')
                ->modalHeading('Award Achievement')
                ->visible(fn (): bool => AchievementResource::canAward())
                ->authorize(fn (): bool => AchievementResource::canAward())
                ->form([
                    AccountSelect::make()->multiple()->label('Account')->required()->dehydrated()
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->id})")
                        ->rules([fn () => function (string $attribute, $value, $fail) {
                            if (in_array(auth()->user()->id, $value)) {
                                $fail('You cannot award achievements to yourself.');
                            }
                        },
                        ]),
                    CheckboxList::make('achievements')
                        ->options(Achievement::query()->orderBy('name')->get()->mapWithKeys(fn ($achievement): array => [$achievement->id => new HtmlString(view('filament.pages.achievements.achievement-option', ['achievement' => $achievement])->render())])->toArray())
                        ->columns(3)
                        ->searchable()
                        ->bulkToggleable()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    foreach ($data['account_id'] as $accountId) {
                        foreach ($data['achievements'] as $achievementId) {
                            $award = AchievementAward::withTrashed()->firstOrNew([
                                'achievement_id' => $achievementId,
                                'account_id' => $accountId,
                            ]);

                            if ($award->exists && ! $award->trashed()) {
                                continue; // Skip if the award already exists and is not soft-deleted
                            }

                            if ($award->trashed()) {
                                $award->restore();
                            } // If the award was previously soft-deleted, restore it instead of creating a new record

                            $award->created_by = auth()->user()->id;
                            $award->updated_by = auth()->user()->id;
                            $award->created_at = now();
                            $award->updated_at = now();
                            $award->deleted_by = null;
                            $award->deleted_at = null;

                            $award->save();

                        }
                    }
                    Notification::make()
                        ->title('Achievements Awarded')
                        ->success()
                        ->send();
                })
                ->modalSubmitActionLabel('Award'),
        ];
    }
}
