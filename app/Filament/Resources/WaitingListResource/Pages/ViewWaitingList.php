<?php

namespace App\Filament\Resources\WaitingListResource\Pages;

use App\Filament\Resources\WaitingListResource;
use App\Models\Atc\Endorsement;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList\WaitingListFlag;
use App\Rules\HomeMemberId;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWaitingList extends ViewRecord
{
    protected static string $resource = WaitingListResource::class;

    protected function getActions(): array
    {
        return [
            Actions\Action::make('add_student')
                ->label('Add Student')
                ->action(function ($data, $action) {
                    $account = Account::find($data['account_id']);
                    $this->record->addToWaitingList($account, auth()->user());

                    $action->success();
                })
                ->after(fn ($livewire) => $livewire->emit('refreshWaitingList'))
                ->form([
                    TextInput::make('account_id')
                        ->label('Account CID')
                        ->rules([new HomeMemberId, fn () => function ($attribute, $value, $fail) {
                            if ($this->record->accounts->contains('id', $value)) {
                                $fail('This account is already in this waiting list.');
                            }
                        }])
                        ->required(),
                ]),

            Actions\Action::make('add_flag')
                ->label('Add Flag')
                ->action(function ($data) {
                    $flag = WaitingListFlag::create([
                        'name' => $data['name'],
                        'endorsement_id' => $data['endorsement_id'],
                    ]);

                    $this->record->addFlag($flag);
                })->form([
                    TextInput::make('name')->rules('required', 'min:3', 'unique:training_waiting_list_flags,name'),

                    Select::make('endorsement_id')->label('Endorsement')->options(fn () => Endorsement::all()->mapWithKeys(function ($item) {
                        return [$item['id'] => $item['name']];
                    }))->hint('If an option is chosen here, this will be an automated flag. This cannot be reversed.'),
                ]),

            Actions\DeleteAction::make(),
        ];
    }
}
