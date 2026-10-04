<?php

namespace App\Models\VisitTransfer;

use App\Models\Mship\Account;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitingRemoval extends Model
{
    use HasFactory;

    protected $table = 'visiting_removals';

    protected $fillable = ['account_id', 'reason', 'removed_at'];

    protected $casts = [
        'removed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }
}
