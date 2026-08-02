<?php

namespace App\Filament\Admin\Pages\VisitTransfer\Widgets;

use App\Models\Training\WaitingList;
use Filament\Widgets\Widget;

class VTWaitingListWidget extends Widget
{
    protected string $view = 'filament.widgets.visit-transfer.vt-waiting-list-widget';

    public WaitingList $list;

    protected int|string|array $columnSpan = 1;

    public function getCount(): int
    {
        return $this->list->waitingListAccounts()->count();
    }

    public function getViewUrl(): string
    {
        return route('filament.training.resources.waiting-lists.view', $this->list);
    }
}
