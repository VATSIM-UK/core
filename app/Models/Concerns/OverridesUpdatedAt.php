<?php

namespace App\Models\Concerns;

/**
 * For use with models which have no updated_at column.
 */
trait OverridesUpdatedAt
{
    public function setUpdatedAt($value)
    {
        // do nothing
    }

    public function getUpdatedAtColumn() {}
}
