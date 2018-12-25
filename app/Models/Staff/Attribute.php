<?php

namespace App\Models\Staff;

use App\Models\Model;

/**
 * App\Models\Staff\Attribute
 *
 * @property int $id
 * @property int $service_id
 * @property string $name
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Staff\Position[] $positions
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Staff\Attribute whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Staff\Attribute whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Staff\Attribute whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Staff\Attribute whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Staff\Attribute whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Attribute extends Model
{
    protected $table = 'staff_attributes';
    protected $primaryKey = 'id';

    public function positions()
    {
        return $this->belongsToMany(\App\Models\Staff\Position::class, 'staff_attribute_position');
    }
}
