<?php

namespace App\Nova\Actions\Training;

use App\Models\Atc\Endorsement;
use App\Models\Training\WaitingList\WaitingListFlag;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;

class AddFlagToWaitingList extends Action
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $name = 'Add Flag';

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        /** @var \App\Models\Training\WaitingList */
        $waitingList = $models->first();

        $flag = WaitingListFlag::create(
            [
                'name' => $fields->name,
                'endorsement_id' => $fields->endorsement_id,
            ]
        );

        $waitingList->addFlag($flag);
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Text::make('Name')->rules('required', 'min:3', 'unique:training_waiting_list_flags,name'),

            Select::make('Endorsement', 'endorsement_id')->options(
                Endorsement::all()->mapWithKeys(function ($item) {
                    return [$item['id'] => $item['name']];
                })
            )->help('If an option is chosen here, this will be an automated flag. This cannot be reversed.'),
        ];
    }
}
