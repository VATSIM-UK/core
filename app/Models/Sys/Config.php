<?php

namespace App\Models\Sys;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $table = 'sys_config';
    protected $primaryKey = 'key';
    protected $fillable = ['value'];
    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('active', '=', '1');
        });
    }
}
