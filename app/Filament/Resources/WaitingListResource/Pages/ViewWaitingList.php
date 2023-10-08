<?php

namespace App\Filament\Resources\WaitingListResource\Pages;

use App\Filament\Resources\WaitingListResource;
use App\Models\Atc\Endorsement;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList\WaitingListFlag;
use App\Rules\HomeMemberId;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Arr;

class ViewWaitingList extends ViewRecord
{
    protected static string $resource = WaitingListResource::class;

    protected function getActions(): array
    {
        return [
            Actions\Action::make('add_student')
                ->action(function ($data, $action) {
                    $account = Account::find($data['account_id']);
                    $joinDate = Arr::get($data, 'join_date');
                    $createdAt = $joinDate ? new Carbon($joinDate) : null;
                    $this->record->addToWaitingList($account, auth()->user(), $createdAt);

                    $action->success();
                })
                ->successNotificationTitle('Student added to waiting list')
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
                    DatePicker::make('join_date')
                        ->visible(fn () => auth()->user()->can('addAccountsAdmin', $this->record)),
                ])
                ->visible(fn () => auth()->user()->can('addAccounts', $this->record)),

            Actions\Action::make('add_flag')
                ->action(function ($data, $action) {
                    $flag = WaitingListFlag::create([
                        'name' => $data['name'],
                        'endorsement_id' => $data['endorsement_id'],
                    ]);

                    $this->record->addFlag($flag);

                    $action->success();
                })
                ->successNotificationTitle('Flag added to waiting list')
                ->form([
                    TextInput::make('name')->rules(['required', 'min:3', 'unique:training_waiting_list_flags,name']),

                    Select::make('endorsement_id')->label('Endorsement')->options(fn () => Endorsement::all()->mapWithKeys(function ($item) {
                        return [$item['id'] => $item['name']];
                    }))->hint('If an option is chosen here, this will be an automated flag. This cannot be reversed.'),
                ])
                ->visible(fn () => auth()->user()->can('addFlags', $this->record)),
        ];
    }
}
