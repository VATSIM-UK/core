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
            'googleUrl' => $this->googleUrl(),
            'outlookUrl' => $this->outlookUrl(),
            'webcalUrl' => $this->webcalUrl(),
        ]);
    }

    private function googleUrl(): ?string
    {
        $webcalUrl = $this->webcalUrl();

        return $webcalUrl === null
            ? null
            : 'https://calendar.google.com/calendar/render?cid='.rawurlencode($webcalUrl);
    }

    private function outlookUrl(): ?string
    {
        if ($this->feedUrl === null) {
            return null;
        }

        return 'https://outlook.live.com/owa/?path=%2Fcalendar%2Faction%2Fcompose&rru=addsubscription'
            .'&url='.rawurlencode($this->feedUrl)
            .'&name='.rawurlencode('VATSIM UK bookings');
    }

    private function webcalUrl(): ?string
    {
        return $this->feedUrl === null
            ? null
            : preg_replace('/^https?:\/\//', 'webcal://', $this->feedUrl);
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
