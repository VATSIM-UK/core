<?php

namespace App\Models\Staff;

/**
 * App\Models\Staff\Service
 *
 * @property int $id
 * @property string $name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Staff\Service whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Staff\Service whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Staff\Service whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Staff\Service whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Service extends \App\Models\Model
{
    protected $table = 'staff_services';
    protected $primaryKey = 'id';
}
