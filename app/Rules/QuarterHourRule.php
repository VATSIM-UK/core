<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class QuarterHourRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $time = Carbon::parse($value);

        if ($time->minute % 15 !== 0 || $time->second !== 0) {
            $fail("The $attribute must be at a 15-minute interval (00, 15, 30 or 45).");
        }
    }
}
