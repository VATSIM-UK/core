<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupSessionStudent extends Model
{
    use HasFactory;

    protected $connection = 'cts';

    protected $table = 'group_sessions_students';

    protected $primaryKey = 'group_sessions_student_id';

    public $timestamps = false;

    public $guarded = [];

    public function groupSession(): BelongsTo
    {
        return $this->belongsTo(GroupSession::class, 'group_session_id', 'group_session_id');
    }
}
