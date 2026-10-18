<?php

declare(strict_types=1);

namespace App\Livewire\Events;

use App\Repositories\Events\EventRepository;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('livewire.events.layout')]
class Index extends Component
{
    use WithPagination;

    public function render(EventRepository $events)
    {
        return view('livewire.events.index', [
            'upcoming' => $events->getUpcoming(),
            'past' => $events->getPast(),
        ])->layoutData([
            '_pageTitle' => 'Events',
            '_breadcrumb' => [
                ['name' => 'Events', 'uri' => route('site.events.index')],
            ],
        ]);
    }
}
