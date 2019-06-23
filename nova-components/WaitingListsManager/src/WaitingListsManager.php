<?php

namespace Vatsimuk\WaitingListsManager;

use Laravel\Nova\ResourceTool;

class WaitingListsManager extends ResourceTool
{
    /**
     * Get the displayable name of the resource tool.
     *
     * @return string
     */
    public function name()
    {
        return 'Waiting List Students';
    }

    public function activeBucket()
    {
        return $this->withMeta(['activeBucket' => true]);
    }

    /**
     * Get the component name for the resource tool.
     *
     * @return string
     */
    public function component()
    {
        return 'waiting-lists-manager';
    }
}
