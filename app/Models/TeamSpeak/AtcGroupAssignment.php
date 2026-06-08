<?php

namespace App\Models\TeamSpeak;

use App\Models\Mship\Account;
use Illuminate\Database\Eloquent\Model;

class AtcGroupAssignment extends Model
{
    protected $table = 'teamspeak_atc_group_assignments';

    protected $fillable = ['account_id', 'atc_server_group_id'];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function serverGroup()
    {
        return $this->belongsTo(AtcServerGroup::class, 'atc_server_group_id');
    }
}
