<?php namespace App\Models\Staff;

/**
 * App\Models\Staff\Attribute
 *
 * @property integer $id
 * @property integer $service_id
 * @property string $name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Staff\Position[] $positions
 */
class Attribute extends \App\Models\aModel
{
    protected $table = 'staff_attributes';
    protected $primaryKey = 'id';

    public function positions()
    {
        return $this->belongsToMany('\App\Models\Staff\Position', 'staff_attribute_position');
    }
}
