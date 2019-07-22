<?php

namespace App\Nova\Actions\Training;

use App\Models\Mship\Account;
use App\Services\Training\AddToWaitingList;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;

class AddStudentToWaitingList extends Action
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $name = 'Add Student';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        /** @var \App\Models\Training\WaitingList $waitingList */
        $waitingList = $models->first();

        $cid = $fields->cid;

        try {
            $account = Account::findOrFail($cid);
        } catch (ModelNotFoundException $e) {
            return Action::danger('The specified CID was not found.');
        }

        if ($waitingList->accounts->contains($account)) {
            return Action::danger('The account already exists in the waiting lists');
        }

        handleService(new AddToWaitingList($waitingList, $account, Account::find(auth()->id())));

        return Action::message('Student added to Waiting List.');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Text::make('CID')->rules('required'),
        ];
    }
}
