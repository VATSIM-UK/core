<?php namespace App\Models\Staff;

/**
 * App\Models\Staff\Service
 *
 * @property integer $id
 * @property string $name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Service extends \App\Models\aModel
{
    protected $table = 'staff_services';
    protected $primaryKey = 'id';
}
