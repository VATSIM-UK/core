<?php namespace App\Models\Staff;

/**
 * App\Models\Staff\Position
 *
 * @property integer $id
 * @property integer $parent_id
 * @property string $type
 * @property string $name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Staff\Position $parent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Staff\Position[] $children
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Staff\Attribute[] $attributes
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Account[] $filledBy
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Staff\Position whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Staff\Position whereParentId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Staff\Position whereType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Staff\Position whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Staff\Position whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Staff\Position whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Staff\Position departments()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Staff\Position positions()
 * @mixin \Eloquent
 */
class Position extends \App\Models\Model
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
        return $this->belongsTo('\App\Models\Staff\Position');
    }

    public function children()
    {
        return $this->hasMany('\App\Models\Staff\Position', 'parent_id', 'id');
    }

    public function attributes()
    {
        return $this->belongsToMany('\App\Models\Staff\Attribute', 'staff_attribute_position');
    }

    public function filledBy()
    {
        return $this->belongsToMany('\App\Models\Mship\Account', 'staff_account_position');
    }

    /**
     * Calculates the depth of the specified position.
     *
     * Uses a collection of all the positions to decrease database calls. Where
     * possible, a collection of all positions should be provided, especially
     * when making repeated calls to this function.
     *
     * @param  \App\Models\Staff\Position                    $position
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
