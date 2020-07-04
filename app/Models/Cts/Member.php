<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $connection = 'cts';
    protected $attributes = ['old_rts_id' => 0];
    protected $guarded = [];

    public $timestamps = false;
    public $incrementing = false;

    public static function findByCID(int $cid): self
    {
        return self::where(compact('cid'))->first();
    }
}
