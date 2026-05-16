<?php

namespace App\Models\Cts;

use App\Models\Mship\Account;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    use HasFactory;

    protected $connection = 'cts';

    protected $attributes = ['old_rts_id' => 0];

    protected $guarded = [];

    public $timestamps = false;

    public $incrementing = false;

    public function account(): Attribute
    {
        return Attribute::make(
            get: fn () => Account::find($this->cid),
        );
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(Availability::class, 'student_id', 'id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class, 'student_id', 'id');
    }
}
