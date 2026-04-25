<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CancelReason extends Model
{
    use HasFactory;

    protected $connection = 'cts';

    protected $table = 'cancel_reason';

    protected $guarded = [];

    public const CREATED_AT = 'date';

    public const UPDATED_AT = null;

    protected $attributes = [
        'used' => 0,
        'sesh_id' => 0,
        'reason_by' => 0,
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'reason_by', 'id');
    }

    public function sesh()
    {
        return $this->morphTo('sesh', 'sesh_type', 'sesh_id');
    }

    public function isExam(): bool
    {
        return $this->sesh_type === 'EX';
    }

    public function isMentoring(): bool
    {
        return $this->sesh_type === 'ME';
    }
}
