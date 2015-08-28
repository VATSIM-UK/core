<?php

namespace App\Models\Mship\Account;

use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

class Note extends \App\Models\aModel
{

    use SoftDeletingTrait;

    protected $table      = "mship_account_note";
    protected $primaryKey = "account_note_id";
    protected $dates      = ['created_at', 'updated_at', 'deleted_at'];
    protected $touches    = ['account'];

    public function account()
    {
        return $this->belongsTo("\App\Models\Mship\Account", "account_id", "account_id");
    }

    public function writer()
    {
        return $this->belongsTo("\App\Models\Mship\Account", "writer_id", "account_id");
    }

    public function type()
    {
        return $this->belongsTo("\App\Models\Mship\Note\Type", "note_type_id", "note_type_id");
    }
}
