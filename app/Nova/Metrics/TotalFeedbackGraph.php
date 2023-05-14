<?php

namespace App\Nova\Metrics;

use App\Models\Mship\Feedback\Feedback;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Trend;

class TotalFeedbackGraph extends Trend
{
    public $name = 'Total Feedback Trends';

    /**
     * Calculate the value of the metric.
     *
     * @return mixed
     */
    public function calculate(Request $request)
    {
        return $this->countByDays($request, Feedback::class);
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            30 => '30 Days',
            60 => '60 Days',
            90 => '90 Days',
        ];
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
        return 'total-feedback-graph';
    }
}
