<?php

namespace App\Models\Cts;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $connection = 'cts';

    protected $guarded = [];

    public const CREATED_AT = 'add_date';

    public const UPDATED_AT = null;

    public function member()
    {
        return $this->belongsTo(Member::class, 'add_by', 'id');
    }

    public function getFromAttribute($value)
    {
        return $this->formatTime($value);
    }

    public function getToAttribute($value)
    {
        return $this->formatTime($value);
    }

    private function formatTime($time)
    {
        return Carbon::parse($time)->format('H:i');
    }
}
