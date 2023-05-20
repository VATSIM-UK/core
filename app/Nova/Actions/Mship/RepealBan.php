<?php

namespace App\Nova\Actions\Mship;

use App\Models\Mship\Note\Type;
use App\Services\Mship\AddNote;
use App\Services\Mship\RepealBan as RepealBanService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Textarea;

class RepealBan extends Action
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        /** @var \App\Models\Mship\Account\Ban $ban */
        $ban = $models->first();

        // Add note before repealing the ban...
        handleService(new AddNote($ban->account, Type::isShortCode('discipline')->first(), Auth::user(),
            $fields->reason));

        // Repeal ban from account...
        handleService(new RepealBanService($ban));

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
