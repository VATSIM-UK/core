<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupSession extends Model
{
    use HasFactory;

    protected $connection = 'cts';

    protected $table = 'group_sessions';

    protected $primaryKey = 'group_session_id';

    public $timestamps = false;

    public $guarded = [];

    public function students(): HasMany
    {
        return $this->hasMany(GroupSessionStudent::class, 'group_session_id', 'group_session_id');
    }
}
