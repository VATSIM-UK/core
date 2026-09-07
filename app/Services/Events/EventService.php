<?php

namespace App\Services\Events;

use App\Enums\EventChecklistItem;
use App\Models\Events\Event;
use App\Models\Events\EventChecklistCompletion;
use App\Models\Mship\Account;

class EventService
{
    /**
     * Republishing deliberately moves `published_at` forward.
     *
     * @return array<int, string> the checklist items still outstanding
     */
    public function publish(Event $event, Account $publisher): array
    {
        $event->published_at = now();
        $event->published_by = $publisher->id;
        $event->save();

        return $event->unpublishedChecklist();
    }

    /**
     * Only newly ticked items are attributed to $account, so unticking and
     * reticking reattributes an item to whoever reticked it.
     *
     * @param  array<int, string>  $itemValues
     */
    public function syncChecklist(Event $event, array $itemValues, Account $account): void
    {
        $ticked = array_values(array_intersect(EventChecklistItem::values(), $itemValues));
        $existing = $event->completedChecklistItems();

        $event->checklistCompletions()
            ->whereIn('item', array_diff($existing, $ticked))
            ->delete();

        foreach (array_diff($ticked, $existing) as $item) {
            $event->checklistCompletions()->create([
                'item' => $item,
                'account_id' => $account->id,
                'completed_at' => now(),
            ]);
        }

        $event->unsetRelation('checklistCompletions');
    }

    public function completionLabel(?EventChecklistCompletion $completion): ?string
    {
        if ($completion === null) {
            return null;
        }

        $account = $completion->account;
        $who = $account ? "{$account->name} ({$account->id})" : 'unknown';

        return "{$who} · ".$completion->completed_at->format(Event::DATETIME_FORMAT);
    }
}
