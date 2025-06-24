<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TheoryManagement extends Model
{
    use HasFactory;

    protected $connection = 'cts';

    protected $table = 'theory_settings';

    public $timestamps = false;

    protected $fillable = ['item', 'setting'];

    public function questions()
    {
        return $this->hasMany(\App\Models\Cts\TheoryQuestion::class, 'level', 'level');
    }
}
