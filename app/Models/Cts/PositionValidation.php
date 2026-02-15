<?php

namespace App\Models\Cts;

use App\Enums\PositionValidationStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PositionValidation extends Model
{
    use HasFactory;

    protected $connection = 'cts';

    protected $table = 'position_validations';

    public $timestamps = false;

    protected $casts = [
        'status' => PositionValidationStatusEnum::class,
    ];

    protected $guarded = [];

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
        return $query->where('status', PositionValidationStatusEnum::Mentor->value);
    }

    public function scopeStudents($query)
    {
        return $query->where('status', PositionValidationStatusEnum::Student->value);
    }
}
