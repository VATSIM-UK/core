<?php

namespace App\Models\Staff;

/**
 * App\Models\Staff\Attribute
 *
 * @property int $id
 * @property int $service_id
 * @property string $name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Staff\Position[] $positions
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Staff\Attribute whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Staff\Attribute whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Staff\Attribute whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Staff\Attribute whereServiceId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Staff\Attribute whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Attribute extends \App\Models\Model
{
    protected $table = 'staff_attributes';
    protected $primaryKey = 'id';

    public function positions()
    {
        return $this->belongsToMany(\App\Models\Staff\Position::class, 'staff_attribute_position');
    }
}
