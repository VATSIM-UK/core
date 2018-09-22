<?php

namespace App\Nova\Actions\Mship;

use App\Models\Mship\Note\Type;
use App\Notifications\Mship\BanRepealed;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Textarea;

class RepealBan extends Action
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        /** @var \App\Models\Mship\Account\Ban $ban */
        $ban = $models->first();

        $note = $ban->account->addNote(Type::isShortCode('discipline')->first(), $fields->reason, auth()->id());
        $ban->notes()->save($note);

        $ban->repeal();

        $ban->account->notify(new BanRepealed($ban));

        return Action::message('Ban repealed successfully.');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Textarea::make('Reason')->rules(['required', 'min:5']),
        ];
    }
}
