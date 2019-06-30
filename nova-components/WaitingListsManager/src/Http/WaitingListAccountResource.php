<?php

namespace Vatsimuk\WaitingListsManager\Http;

use Illuminate\Http\Resources\Json\JsonResource;

class WaitingListAccountResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'pivot_id' => $this->pivot->id,
            'name' => $this->name,
            'position' => $this->pivot->position,
            'atcHourCheck' => $this->pivot->atcHourCheck,
            'created_at' => $this->pivot->created_at,
            'status' => new WaitingListStatusResource($this->pivot->status->first()),
            'flags' => $this->pivot->flags,
            'notes' => $this->pivot->notes
        ];
    }
}
