<?php

namespace App\Filament\Resources\WaitingListResource\Pages;

use App\Filament\Resources\WaitingListResource;
use App\Filament\Resources\WaitingListResource\Widgets\IndividualWaitingListOverview;
use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListFlag;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;

/**
 * @property WaitingList $record
 */
class ViewWaitingList extends ViewRecord
{
    protected static string $resource = WaitingListResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            IndividualWaitingListOverview::class,
        ];
    }

    protected function getHeaderActions(): array
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
                ->after(fn ($livewire) => $livewire->dispatch('refreshWaitingList'))
                ->form([
                    TextInput::make('account_id')
                        ->label('Account CID')
                        ->rule(fn () => function ($attribute, $value, $fail) {
                            if ($this->record->includesAccount($value)) {
                                $fail('This account is already in this waiting list.');
                            }
                        })
                        ->rule(fn () => function ($attribute, $value, $fail) {
                            if ($this->record->home_members_only) {
                                try {
                                    if (! Account::findOrFail($value)->primary_state->isDivision) {
                                        $fail('The specified member is not a home UK member.');
                                    }
                                } catch (ModelNotFoundException $e) {
                                    $fail('The specified member was not found.');
                                }
                            }
                        })
                        ->required(),
                    DatePicker::make('join_date')
                        ->visible(fn () => auth()->user()->can('addAccountsAdmin', $this->record))
                        ->helperText('This field should only be used to override the date a member joined the waiting list. It is only available to admin-level users..'),
                ])
                ->visible(fn () => auth()->user()->can('addAccounts', $this->record)),

            Actions\Action::make('add_flag')
                ->action(function ($data, $action) {
                    $flag = WaitingListFlag::create([
                        'name' => $data['name'],
                        'position_group_id' => $data['position_group_id'],
                        'display_in_table' => $data['display_in_table'] ?? false,
                    ]);

                    $this->record->addFlag($flag);

                    $action->success();
                })
                ->successNotificationTitle('Flag added to waiting list')
                ->form([
                    TextInput::make('name')->rules(['required', 'min:3', 'unique:training_waiting_list_flags,name']),

                    Select::make('position_group_id')->label('Position Group')->options(fn () => PositionGroup::all()->mapWithKeys(function ($item) {
                        return [$item['id'] => $item['name']];
                    }))->hint('If an option is chosen here, this will be an automated flag. This cannot be reversed.'),

                    Toggle::make('display_in_table')
                        ->label('Display in Waiting List Table')
                        ->default(false),
                ])
                ->visible(fn () => auth()->user()->can('addFlags', $this->record)),
        ];
    }
}
