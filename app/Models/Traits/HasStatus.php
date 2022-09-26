<?php

namespace App\Models\Traits;

trait HasStatus
{
    /**
     * Check if the current status of the model is the given status.
     *
     * @param  int  $status
     * @return bool
     */
    public function isStatus($status)
    {
        return $this->status == $status;
    }

    /**
     * Check if the current status of the model is in the given list of stati.
     *
     * @param  array  $stati  List to check against
     * @return bool
     */
    public function isStatusIn($stati)
    {
        return in_array($this->status, $stati);
    }

    /**
     * Check if the current status of the model is NOT in the given list of stati.
     *
     * @param  array  $stati  List to check against
     * @return bool
     */
    public function isStatusNotIn($stati)
    {
        return ! $this->isStatusIn($stati);
    }
}
