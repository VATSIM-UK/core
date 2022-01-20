<?php

namespace App\View\Components;

use Illuminate\View\Component;

class BooleanIndicator extends Component
{
    public bool $value; // The value being represented
    public bool $positive; // Whether true is regarded as positive

    /**
     * Create a new component instance.
     *
     * @param $value
     * @param bool $positive
     */
    public function __construct($value, $positive = true)
    {
        $this->value = $value;
        $this->positive = $positive;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.boolean-indicator');
    }
}
