<?php

namespace App\Filament\Admin\Resources\Bans\Pages;

use App\Filament\Admin\Helpers\Pages\BaseViewRecordPage;
use App\Filament\Admin\Resources\Bans\BanResource;
use App\Models\Mship\Note\Type;
use App\Notifications\Mship\BanModified;
use App\Notifications\Mship\BanRepealed;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;

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
                ->schema([
                    DateTimePicker::make('period_finish')->label('Finish Time')->default($this->record->period_finish)->nullable()->notIn($this->record->period_finish ?? ''),
                    Grid::make(2)->columnSpanFull()->schema([
                        Textarea::make('extra_info')
                            ->required()
                            ->label('Reason')
                            ->helperText('This is sent to the member')
                            ->minLength(5),
                        Textarea::make('note')
                            ->helperText('This is **not** sent to the member')
                            ->required()
                            ->minLength(5),
                    ]),
                ])
                ->action(function (array $data, Action $action) {
                    $oldFinish = $this->record->period_finish;
                    $newFinish = $data['period_finish'] ? new Carbon($data['period_finish']) : null;

                    if ($newFinish === null) {
                        $noteComment = 'Ban has been made permanent.'."\n";
                    } elseif ($oldFinish === null) {
                        $noteComment = 'Ban now has an expiry: '.$newFinish->toDateTimeString()."\n";
                    } elseif ($oldFinish->gt($newFinish)) {
                        $noteComment = 'Ban has been reduced from '.$oldFinish->toDateTimeString().".\n";
                        $noteComment .= 'New finish: '.$newFinish->toDateTimeString()."\n";
                    } else {
                        $noteComment = 'Ban has been extended from '.$oldFinish->toDateTimeString().".\n";
                        $noteComment .= 'New finish: '.$newFinish->toDateTimeString()."\n";
                    }
                    $noteComment .= $data['note'];

                    // Attach the note.
                    $this->record->account->addNote(Type::isShortCode('discipline')->first(), $noteComment, auth()->user(), $this->record);

                    // Modify the ban
                    $this->record->reason_extra = $this->record->reason_extra."\n".$data['extra_info'];
                    $this->record->period_finish = $newFinish;
                    $this->record->save();

                    $this->record->account->notify(new BanModified($this->record));

                    $this->fillForm();
                    $action->success();
                })->successNotificationTitle('Ban updated'),

            Action::make('repeal')->label('Repeal')
                ->color('danger')
                ->visible(auth()->user()->can('repeal', $this->record))
                ->requiresConfirmation()
                ->schema([
                    Textarea::make('reason')->required()->minLength(5),
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
