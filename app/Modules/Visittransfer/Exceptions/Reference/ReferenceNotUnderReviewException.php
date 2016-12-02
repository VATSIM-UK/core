<?php

namespace App\Modules\Visittransfer\Exceptions\Reference;

use App\Modules\Visittransfer\Models\Reference;

class ReferenceNotUnderReviewException extends \Exception
{
    private $reference;

    public function __construct(Reference $reference)
    {
        $this->reference = $reference;

        $this->message = 'This reference is not under review and as such cannot be accepted/rejected.';
    }

    public function __toString()
    {
        return $this->message;
    }
}
