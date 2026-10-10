<?php

namespace App\Models\Mship\Account;

use App\Models\Model;
use App\Models\Mship\Account;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvailabilitySetting extends Model
{
    public const DEFAULT_FROM = '18:00';

    public const DEFAULT_TO = '21:00';

    protected $table = 'training_availability_settings';

    protected $fillable = [
        'account_id',
        'from',
        'to',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}
