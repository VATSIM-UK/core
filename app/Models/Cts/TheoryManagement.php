<?php

namespace App\Models\Cts;

use App\Models\Mship\Account;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use App\Models\Cts\Member;

class TheoryManagement extends Model
{
    use HasFactory;

    protected $connection = 'cts';

    protected $table = 'theory_settings';

    public $timestamps = false;
    
    protected $fillable = ['item', 'setting'];

    /**
     * Get result for the internal Core account_id, also their CId
     * and handle the logic where a member_id in CTS is different to that
     * of the account_id in Core.
     */
    


     public function questions() {
        return $this->hasMany(\App\Models\Cts\TheoryQuestion::class, 'level', 'level');
     }
}
