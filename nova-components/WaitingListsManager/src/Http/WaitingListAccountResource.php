<?php

namespace Vatsimuk\WaitingListsManager\Http;

use Illuminate\Http\Resources\Json\JsonResource;

class WaitingListAccountResource extends JsonResource
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'pivot_id' => $this->pivot->id,
            'name' => $this->name,
            'atcHourCheck' => $this->pivot->eligibility_summary['base_controlling_hours'] ?? false,
            'created_at' => $this->pivot->created_at,
            'status' => new WaitingListStatusResource($this->pivot->current_status),
            'flags' => $this->pivot->flags,
            'notes' => $this->pivot->notes,
            'theory_exam_passed' => $this->pivot->theory_exam_passed,
        ];
    }
}
