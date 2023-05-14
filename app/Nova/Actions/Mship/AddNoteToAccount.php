<?php

namespace App\Nova\Actions\Mship;

use App\Models\Mship\Note\Type;
use App\Services\Mship\AddNote;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Textarea;

class AddNoteToAccount extends Action
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $name = 'Add Note';

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        /** @var \App\Models\Mship\Account $account */
        $account = $models->first();

        /** @var \App\Models\Mship\Note\Type $type */
        $type = $fields->type;

        handleService(new AddNote($account, $type, Auth::user(), $fields->content));

        return Action::message('Note added to Account.');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Select::make('Note Type', 'type_id')->options(
                Type::isAvailable()->get()->mapWithKeys(function ($item) {
                    return [$item['id'] => $item['name']];
                })
            )->rules('required'),

            Textarea::make('Content')->onlyOnForms()->rules('required'),
        ];
    }
}
