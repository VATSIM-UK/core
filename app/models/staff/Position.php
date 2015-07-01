<?php namespace Models\Staff;

class Position extends \Models\aModel
{
    protected $table = 'staff_positions';
    protected $primaryKey = 'id';

    public function scopeDepartments($query)
    {
        return $query->where('type', 'D');
    }

    public function scopePositions($query)
    {
        return $query->where('type', 'P');
    }

    public function parent()
    {
        return $this->belongsTo('\Models\Staff\Position');
    }

    public function children()
    {
        return $this->hasMany('\Models\Staff\Position', 'parent_id', 'id');
    }

    public function attributes()
    {
        return $this->belongsToMany('\Models\Staff\Attribute', 'staff_attribute_position');
    }

    public function filledBy()
    {
        return $this->belongsToMany('\Models\Mship\Account', 'staff_account_position');
    }

    /**
     * Calculates the depth of the specified position.
     *
     * Uses a collection of all the positions to decrease database calls. Where
     * possible, a collection of all positions should be provided, especially
     * when making repeated calls to this function.
     *
     * @param  \Models\Staff\Position                    $position
     * @param  \Illuminate\Database\Eloquent\Collection  $all_positions
     * @return integer
     */
    public static function totalParents($position, $all_positions = null)
    {
        if ($all_positions === null) {
            $all_positions = Position::all();
        }

        $count = 0;
        while ($position->parent_id !== null) {
            $count++;
            $position = $all_positions->find($position->parent_id);
        }

        return $count;
    }
}
