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

    public function mentor()
    {
        return $this->belongsTo(Member::class, 'mentor_id', 'id');
    }

    public function student()
    {
        return $this->belongsTo(Member::class, 'student_id', 'id');
    }
}
