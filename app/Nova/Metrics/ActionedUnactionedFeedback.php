<?php

namespace App\Nova\Metrics;

use App\Models\Mship\Feedback\Feedback;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Partition;

class ActionedUnactionedFeedback extends Partition
{
    public $name = 'Actioned vs Unactioned Feedback';

    /**
     * Calculate the value of the metric.
     *
     * @return mixed
     */
    public function calculate(Request $request)
    {
        return $this->result([
            'Actioned' => Feedback::actioned()->count(),
            'Un-Actioned' => Feedback::unActioned()->count(),
        ])->colors([
            'Actioned' => '#2ECC40',
            'Un-Actioned' => '#FF4136',
        ]);
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        return now()->addMinutes(15);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'actioned-unactioned-feedback';
    }
}
