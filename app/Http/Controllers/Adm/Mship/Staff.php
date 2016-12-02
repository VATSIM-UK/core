<?php

namespace App\Http\Controllers\Adm\Mship;

use App\Models\Staff\Position;

class Staff extends \App\Http\Controllers\Adm\AdmController
{
    protected $ordered_positions = [];

    public function getIndex()
    {
        $positions = Position::with('attributes', 'filledBy')->get();
        $this->expandPosition($positions->first(), $positions);

        return $this->viewMake('adm.mship.staff.index')
                    ->with('positions', $positions)
                    ->with('ordered_positions', $this->ordered_positions);
    }

    protected function expandPosition($position, $all_positions)
    {
        $this->ordered_positions[] = $position;
        if ($position->type == 'D') {
            $children = $all_positions->filter(function ($child) use ($position) {
                return $child->parent_id === $position->id;
            });
            $child_positions = $children->filter(function ($position) {
                return $position->type === 'P';
            });
            $child_departments = $children->filter(function ($position) {
                return $position->type === 'D';
            });

            foreach ($child_positions as $child) {
                $this->expandPosition($child, $all_positions);
            }
            foreach ($child_departments as $child) {
                $this->expandPosition($child, $all_positions);
            }
        }
    }
}
