<?php

namespace App\Filament\Admin\Pages\ATCTraining\Widgets;

use App\Services\Admin\ATCTrainingStats;
use Filament\Widgets\Widget;

class RosterWidget extends Widget
{
    protected string $view = 'filament.pages.atc-training.widgets.roster-widget';

    protected array $ratingOrder = ['S1', 'S2', 'S3', 'C1', 'C3'];

    public function getRows(): array
    {
        $data = ATCTrainingStats::rosterCountByRating();

        // Order the rows by the specified rating order
        usort($data, function ($a, $b) {
            $indexA = array_search($a['rating'], $this->ratingOrder);
            $indexB = array_search($b['rating'], $this->ratingOrder);

            return $indexA <=> $indexB;
        });

        return $data;
    }

    public function getTotalCount(): int
    {
        return collect($this->getRows())->sum('count');
    }
}
