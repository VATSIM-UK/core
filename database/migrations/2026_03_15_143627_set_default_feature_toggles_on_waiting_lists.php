<?php

use App\Models\Training\WaitingList;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        WaitingList::get()->each(function (WaitingList $waitingList) {
            $toggles = $waitingList->feature_toggles ?? [];

            if (! array_key_exists('show_recent_controlling', $toggles)) {
                $toggles['show_recent_controlling'] = true;
            }

            if (! array_key_exists('show_solo_endorsement', $toggles)) {
                $toggles['show_solo_endorsement'] = true;
            }

            $waitingList->feature_toggles = $toggles;
            $waitingList->save();
        });
    }

    public function down(): void
    {
        WaitingList::get()->each(function (WaitingList $waitingList) {
            $toggles = $waitingList->feature_toggles ?? [];

            unset($toggles['show_recent_controlling'], $toggles['show_solo_endorsement']);

            $waitingList->feature_toggles = empty($toggles) ? null : $toggles;
            $waitingList->save();
        });
    }
};
