<?php

namespace App\Filament\Admin\Pages\ATCTraining\Widgets;

use App\Services\Admin\ATCTrainingStats;
use Filament\Widgets\Widget;

class EndorsementWidget extends Widget
{
    protected string $view = 'filament.pages.atc-training.widgets.endorsement-widget';

    protected string $position;

    protected array $ratingOrder = ['S1', 'S2', 'S3', 'C1', 'C3'];

    public function mount(string $position): void
    {
        $this->position = $position;
    }

    public function getRows(): array
    {
        $data = ATCTrainingStats::endorsementHolders($this->position);
        $rows = [];

        foreach ($data as $group) {
            foreach ($group['endorsements'] as $endorsement) {
                $rows[] = [
                    'rating' => $group['rating'],
                    'endorsement' => $endorsement['endorsement'],
                    'count' => $endorsement['count'],
                ];
            }
        }

        // Order the rows by the specified rating order
        usort($rows, function ($a, $b) {
            $indexA = array_search($a['rating'], $this->ratingOrder);
            $indexB = array_search($b['rating'], $this->ratingOrder);

            return $indexA <=> $indexB;
        });

        return $rows;
    }

    public function getEndorsementCount(): int
    {
        return collect($this->getRows())->sum('count');
    }
}
