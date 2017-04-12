<?php

namespace App\Modules\Visittransfer\Models\Facility;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Visittransfer\Models\Facility\Email
 *
 * @property int $id
 * @property int $facility_id
 * @property string $email
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Visittransfer\Models\Facility\Email whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Visittransfer\Models\Facility\Email whereEmail($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Visittransfer\Models\Facility\Email whereFacilityId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Visittransfer\Models\Facility\Email whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Modules\Visittransfer\Models\Facility\Email whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Email extends Model
{
    protected $table = 'vt_facility_email';
    protected $primaryKey = 'id';

    public $timestamps = true;
    public $fillable = [
        'facility_id',
        'email',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function facility()
    {
        $this->belongsTo(\App\Modules\Visittransfer\Models\Facility::class);
    }
}
