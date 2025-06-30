<?php

namespace App\Models\Cts;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ValidationPosition extends Model
{
    use HasFactory;
    
    protected $connection = 'cts';

    protected $table = 'validations_p';

    public $timestamps = false;

    public function members()
    {
        return $this->belongsToMany(Member::class, 'validations', 'position_id', 'member_id');
    }

    public function scopeWhereName(Builder $query, string $name)
    {
        return $query->where('position', $name);
    }
}
