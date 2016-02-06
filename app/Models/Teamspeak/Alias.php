<?php

namespace App\Models\Teamspeak;

use App\Traits\RecordsActivity;

/**
 * App\Models\Teamspeak\Alias
 *
 * @property integer                                 $id
 * @property integer                                 $account_id
 * @property string                                  $display_name
 * @property string                                  $notes
 * @property \Carbon\Carbon                          $created_at
 * @property \Carbon\Carbon                          $updated_at
 * @property-read \App\Models\Teamspeak\Registration $account
 */
class Alias extends \App\Models\aModel
{
    use RecordsActivity;

    protected $table      = 'teamspeak_alias';
    protected $primaryKey = 'id';
    protected $dates      = ['created_at', 'updated_at'];

    public function account()
    {
        return $this->belongsTo("\App\Models\Teamspeak\Registration", "account_id", "account_id");
    }

}
