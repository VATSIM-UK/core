<?php

namespace App\Filament\Admin\Resources\BanResource\Pages;

use App\Filament\Admin\Helpers\Pages\BaseViewRecordPage;
use App\Filament\Admin\Resources\BanResource;
use App\Models\Mship\Note\Type;
use App\Notifications\Mship\BanModified;
use App\Notifications\Mship\BanRepealed;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\Grid;

class ViewBan extends BaseViewRecordPage
{
    protected static string $resource = BanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Modify')
                ->color('warning')
                ->visible(auth()->user()->can('update', $this->record))
                ->form([
                    Forms\Components\DateTimePicker::make('period_finish')->label('Finish Time')->default($this->record->period_finish)->required()->notIn($this->record->period_finish ?? ''),
                    Grid::make(2)->schema([
                        Forms\Components\Textarea::make('extra_info')
                            ->required()
                            ->label('Reason')
                            ->helperText('This is sent to the member')
                            ->minLength(5),
                        Forms\Components\Textarea::make('note')
                            ->helperText('This is **not** sent to the member')
                            ->required()
                            ->minLength(5),
                    ]),
                ])
                ->action(function (array $data, Action $action) {
                    $finish = new Carbon($data['period_finish']);

                    if ($this->record->period_finish->gt($finish)) {
                        $noteComment = 'Ban has been reduced from '.$this->record->period_finish->toDateTimeString().".\n";
                    } else {
                        $noteComment = 'Ban has been extended from '.$this->record->period_finish->toDateTimeString().".\n";
                    }
                    $noteComment .= 'New finish: '.$finish->toDateTimeString()."\n";
                    $noteComment .= $data['note'];

                    // Attach the note.
                    $this->record->account->addNote(Type::isShortCode('discipline')->first(), $noteComment, auth()->user(), $this->record);

                    // Modify the ban
                    $this->record->reason_extra = $this->record->reason_extra."\n".$data['extra_info'];
                    $this->record->period_finish = $finish;
                    $this->record->save();

                    $this->record->account->notify(new BanModified($this->record));

                    $this->fillForm();
                    $action->success();
                })->successNotificationTitle('Ban updated'),

            Action::make('repeal')->label('Repeal')
                ->color('danger')
                ->visible(auth()->user()->can('repeal', $this->record))
                ->requiresConfirmation()
                ->form([
                    Forms\Components\Textarea::make('reason')->required()->minLength(5),
                ])
                ->action(function (array $data, Action $action) {
                    $this->record->account->addNote(Type::isShortCode('discipline')->first(), 'Ban Repealed: '.$data['reason'], auth()->user(), $this->record);
                    $this->record->repeal();

                    $this->record->account->notify(new BanRepealed($this->record));

                    $action->success();
                    $this->fillForm();
                })
                ->successNotificationTitle('Ban repealed'),
        ];
    }
}
