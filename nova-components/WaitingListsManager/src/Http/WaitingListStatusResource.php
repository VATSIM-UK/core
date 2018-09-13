<?php

namespace Vatsimuk\WaitingListsManager\Http;

use Illuminate\Http\Resources\Json\JsonResource;

class WaitingListStatusResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'name' => $this->name,
            'retains_position' => (bool) $this->retains_position,
        ];
    }
}