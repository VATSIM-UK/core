<?php

namespace App\Models\Teamspeak;

use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

/**
 * App\Models\Teamspeak\Confirmation
 *
 * @property integer                                 $registration_id
 * @property string                                  $privilege_key
 * @property \Carbon\Carbon                          $created_at
 * @property \Carbon\Carbon                          $updated_at
 * @property-read \App\Models\Teamspeak\Registration $registration
 */
class Confirmation extends \App\Models\aModel
{
    use RecordsActivity;

    protected $table      = 'teamspeak_confirmation';
    protected $primaryKey = 'registration_id';

    public function registration()
    {
        return $this->belongsTo("\App\Models\Teamspeak\Registration", "registration_id", "id");
    }

}
