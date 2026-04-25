<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;

    protected $connection = 'cts';

    public $timestamps = false;

    /**
     * CTS assigns numeric primary keys on insert; Eloquent must treat the key as incrementing
     * so create()/factory() hydrate `id` for URLs, Filament table keys, and refresh().
     */
    public $incrementing = true;

    protected $keyType = 'int';

    protected $guarded = [];

    protected $casts = [
        'filed' => 'datetime',
        'cancelled_datetime' => 'datetime',
    ];

    public function mentor()
    {
        return $this->belongsTo(Member::class, 'mentor_id', 'cid');
    }

    public function student()
    {
        return $this->belongsTo(Member::class, 'student_id', 'cid');
    }

    public function cancellation()
    {
        return $this->hasOne(CancelReason::class, 'sesh_id')->where('sesh_type', 'ME');
    }

    public function isCancelled(): bool
    {
        return ! is_null($this->cancelled_datetime);
    }

    public function isNoShow(): bool
    {
        return $this->noShow;
    }
}
