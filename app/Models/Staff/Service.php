<?php

namespace App\Models\Staff;

use App\Models\Model;

/**
 * App\Models\Staff\Service
 *
 * @property int $id
 * @property string $name
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Staff\Service whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Staff\Service whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Staff\Service whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Staff\Service whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Service extends Model
{
    protected $table = 'staff_services';
    protected $primaryKey = 'id';
}
