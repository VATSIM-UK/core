<?php

namespace App\Exceptions\VisitTransferLegacy\Reference;

use App\Models\VisitTransferLegacy\Reference;

class ReferenceNotRequestedException extends \Exception
{
    private $reference;

    public function __construct(Reference $reference)
    {
        $this->reference = $reference;

        $this->message = 'This reference is not requested. It may have already been submitted.';
    }

    public function __toString()
    {
        return $this->message;
    }
}
