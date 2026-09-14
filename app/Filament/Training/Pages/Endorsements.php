<?php

namespace App\Filament\Training\Pages;

use App\Filament\Training\Resources\EndorsementRequests\EndorsementRequestResource;
use App\Filament\Training\Resources\PositionGroups\PositionGroupResource;
use App\Filament\Training\Resources\SoloEndorsements\SoloEndorsementResource;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class Endorsements extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-check-badge';

    protected static string|\UnitEnum|null $navigationGroup = 'Endorsements';

    protected static ?string $navigationLabel = 'Endorsements';

    protected string $view = 'filament.training.pages.endorsements';

    protected const TABS = [
        EndorsementRequestResource::class => 'requests',
        SoloEndorsementResource::class => 'solo',
        PositionGroupResource::class => 'tiers',
    ];

    #[Url]
    public string $activeTab = 'requests';

    public static function canAccess(array $parameters = []): bool
    {
        return EndorsementRequestResource::canAccess() || PositionGroupResource::canAccess() || SoloEndorsementResource::canAccess();
    }

    public static function urlFor(string $resourceClass): string
    {
        return static::getUrl(['activeTab' => static::TABS[$resourceClass] ?? 'requests']);
    }

    public function getTabs(): array
    {
        $tabs = [];

        foreach (static::TABS as $resourceClass => $key) {
            $tabs[$key] = [
                'label' => $resourceClass::getNavigationLabel(),
                'icon' => $resourceClass::getNavigationIcon(),
                'resource' => $resourceClass,
                'visible' => $resourceClass::canAccess(),
            ];
        }

        return $tabs;
    }

    public function mount(): void
    {
        $visibleTabs = collect($this->getTabs())->filter(fn (array $tab) => $tab['visible']);

        abort_unless($visibleTabs->isNotEmpty(), 403);

        if (! $visibleTabs->has($this->activeTab)) {
            $this->activeTab = $visibleTabs->keys()->first();
        }
    }
}
