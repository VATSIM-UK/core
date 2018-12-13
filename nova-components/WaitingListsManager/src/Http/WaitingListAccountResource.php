<?php

namespace Vatsimuk\WaitingListsManager\Http;

use Illuminate\Http\Resources\Json\JsonResource;

class WaitingListAccountResource extends JsonResource
{
    protected $position = 0;

    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'position' => $this->pivot->position,
            'atcHourCheck' => $this->pivot->atcHourCheck,
            'created_at' => $this->pivot->created_at,
            'status' => new WaitingListStatusResource($this->pivot->status->first()),
        ];
    }
}
