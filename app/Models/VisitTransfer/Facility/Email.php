<?php

namespace App\Models\VisitTransfer\Facility;

use App\Models\Model;

/**
 * App\Models\VisitTransfer\Facility\Email.
 *
 * @property int $id
 * @property int $facility_id
 * @property string $email
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Facility\Email whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Facility\Email whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Facility\Email whereFacilityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Facility\Email whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransfer\Facility\Email whereUpdatedAt($value)
 *
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
        $this->belongsTo(\App\Models\VisitTransfer\Facility::class);
    }
}
