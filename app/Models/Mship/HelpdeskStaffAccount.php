<?php

declare(strict_types=1);

namespace App\Models\Mship;

use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HelpdeskStaffAccount extends Model
{
    protected $table = 'mship_helpdesk_staff_account';

    protected $fillable = [
        'account_id',
        'helpdesk_staff_id',
    ];

    protected $casts = [
        'account_id' => 'integer',
        'helpdesk_staff_id' => 'integer',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
