<?php

declare(strict_types=1);

namespace App\Livewire\Bookings;

use App\Models\Mship\Account;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CalendarSubscription extends Component
{
    public ?string $feedUrl = null;

    public function mount(): void
    {
        $account = $this->account();

        if ($account === null) {
            return;
        }

        $this->feedUrl = $this->urlFor($account->ensureCalendarFeedToken());
    }

    public function regenerate(): void
    {
        $account = $this->account();

        abort_if($account === null, 403);

        $this->feedUrl = $this->urlFor($account->regenerateCalendarFeedToken());

        audit('Bookings calendar feed link regenerated', ['account_id' => $account->getKey()]);
    }

    public function render(): View
    {
        return view('livewire.bookings.calendar-subscription', [
            'webcalUrl' => $this->feedUrl === null
                ? null
                : preg_replace('/^https?:\/\//', 'webcal://', $this->feedUrl),
        ]);
    }

    private function account(): ?Account
    {
        $account = auth()->user();

        return $account instanceof Account ? $account : null;
    }

    private function urlFor(string $token): string
    {
        return route('api.bookings.calendar.feed', ['token' => $token]);
    }
}
