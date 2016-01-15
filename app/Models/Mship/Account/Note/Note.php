<?php

namespace App\Models\Mship\Account\Note;

use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

/**
 * App\Models\Mship\Account\Note\Note
 *
 * @property integer $account_note_id
 * @property integer $note_type_id
 * @property integer $account_id
 * @property integer $writer_id
 * @property string $attachment_type
 * @property integer $attachment_id
 * @property string $content
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property-read \App\Models\Mship\Account $user
 * @property-read \App\Models\Mship\Account $actioner
 * @property-read \App\Models\Mship\Account\Note\Flag $flag
 * @property-read \App\Models\Mship\Account\Note\Format $format
 * @property-write mixed $data
 */
class Note extends \Eloquent {
    use SoftDeletingTrait;

    protected $table = "mship_account_note";
    protected $primaryKey = "account_note_id";
    protected $dates = ['created_at', 'deleted_at'];
    protected $hidden = ['account_note_id'];

    public function user(){
        return $this->belongsTo("\App\Models\Mship\Account", "account_id", "account_id");
    }

    public function actioner(){
        return $this->belongsTo("\App\Models\Mship\Account", "account_id", "actioner_id");
    }

    public function flag(){
        return $this->hasOne("\App\Models\Mship\Account\Note\Flag", "account_note_id", "account_note_id");
    }

    public function format(){
        return $this->hasOne("\App\Models\Mship\Account\Note\Format", "account_note_id", "account_note_id");
    }

    public function setDataAttribute($value){
        $this->attributes['data'] = serialize($value);
    }
}
