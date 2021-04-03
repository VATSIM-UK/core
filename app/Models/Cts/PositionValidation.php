<?php

namespace App\Models\Cts;

use App\Models\Cts\Member;
use App\Models\Cts\Position;
use Illuminate\Database\Eloquent\Model;

class PositionValidation extends Model
{
    protected $connection = 'cts';
    protected $table = 'position_validations';

    public $timestamps = false;

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id', 'id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'id');
    }
}
