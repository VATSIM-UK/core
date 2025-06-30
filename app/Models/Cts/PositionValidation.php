<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PositionValidation extends Model
{
    use HasFactory;
    
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

    public function scopeMentors($query)
    {
        return $query->where('status', '=', 5);
    }

    public function scopeStudents($query)
    {
        return $query->where('status', 1);
    }
}
