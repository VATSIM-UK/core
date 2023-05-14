<?php

namespace App\Nova\Actions\Training;

use App\Models\Mship\Account;
use App\Services\Training\AddToWaitingList;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Date;

final class AddStudentToWaitingListAdmin extends AddStudentToWaitingList
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $name = 'Add Student [ADMIN]';

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        /** @var \App\Models\Training\WaitingList $waitingList */
        $waitingList = $models->first();

        try {
            $account = Account::findOrFail($fields->cid);
        } catch (ModelNotFoundException $e) {
            return $this->dangerAction('The specified CID was not found.');
        }

        if ($waitingList->accounts->contains($account)) {
            return $this->dangerAction('The account already exists in the waiting lists');
        }

        $createdAt = $fields->join_date;
        if ($createdAt) {
            $createdAt = Carbon::parse($fields->join_date);
        }

        handleService(new AddToWaitingList($waitingList, $account, Account::find(auth()->id()), $createdAt));

        return Action::message('Student added to Waiting List.');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        $additionalFields = [
            Date::make('Join Date')->help(
                'Optionally specify a join date if you need to fix it to a date
                e.g. member is re-joining a list after deferring their place in training.
                This cannot be todays date'
            )->rules('nullable', 'before:today'),
        ];

        return array_merge(parent::fields(), $additionalFields);
    }
}
