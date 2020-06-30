<?php

namespace App\Models\Training;

use App\Models\Mship\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SessionRequestCheck extends Model
{
    use SoftDeletes;

    protected $fillable = ['account_id', 'rts_id', 'stage'];

    const NO_WARNING_SENT = 0;
    const FIRST_WARNING_SENT = 1;
    const SECOND_WARNING_SENT = 2;
    const TD_NOTIFICATION_SENT = 3;

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function incrementStage(): void
    {
        $this->stage += 1;
        $this->save();
    }
}
