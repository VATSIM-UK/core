<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ProgressIndicator extends Component
{
    public float $value;

    public string $text;

    public float $min;

    public float $max;

    public string $class;

    /**
     * Create a new component instance.
     *
     * @param  string  $text
     * @param  string  $class
     * @param  int  $min
     * @param  int  $max
     */
    public function __construct(float $value, $text = '', $class = '', $min = 0, $max = 100)
    {
        $this->value = $value;
        $this->text = $text;
        $this->min = $min;
        $this->max = $max;
        $this->class = $class;
    }

    public function complete()
    {
        return $this->cappedPercentage() == 100;
    }

    public function cappedPercentage()
    {
        return min(100 * $this->value / $this->max, 100);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.progress-indicator');
    }
}
