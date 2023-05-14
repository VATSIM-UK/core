<?php

namespace App\Nova\Actions\Mship;

use App\Models\Mship\Ban\Reason;
use App\Services\Mship\BanAccount as BanAccountService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

class BanAccount extends Action
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        /** @var \App\Models\Mship\Account $account */
        $account = $models->first();

        if ($account->is_banned) {
            return Action::danger('This Account is already banned');
        }

        handleService(new BanAccountService($account, Reason::find($fields->ban_reason), Auth::user(), $fields->toArray()));

        return Action::message('You have successfully banned this member.');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Select::make('Ban Reason', 'ban_reason')->options(
                Reason::all()->mapWithKeys(function ($item) {
                    return [$item['id'] => $item['name']];
                })
            )->rules('required'),

            Text::make('Ban Notes', 'ban_reason_extra')->help('This will be sent to the user.')->rules('min:5'),

            // formerly called ban_note_content
            Textarea::make('Internal Note', 'ban_internal_note')->rules(['required', 'min:5']),
        ];
    }
}
