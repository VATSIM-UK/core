<?php

namespace App\Nova\Actions\Training;

use App\Models\Training\WaitingList\WaitingListFlag;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Text;

class AddFlagToWaitingList extends Action
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $name = 'Add Flag';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        /** @var \App\Models\Training\WaitingList */
        $waitingList = $models->first();

        $flag = WaitingListFlag::create(['name' => $fields->name, 'default_value' => $fields->default_value]);

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

            Boolean::make('Default Value')->rules('required', 'boolean')
                ->help('Ticking this field with mean that when the flag is assigned, it will default to true. The opposite is also true.')
        ];
    }
}
