<?php

namespace App\View\Components;

use Illuminate\View\Component;

class BooleanIndicator extends Component
{
    public bool $value; // The value being represented

    /**
     * Create a new component instance.
     *
     * @param  bool  $positive
     */
    public function __construct($value)
    {
        $this->value = $value;
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
