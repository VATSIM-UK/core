<?php

namespace App\Models\Mship\Account;

use App\Enums\EmailType;
use App\Models\Mship\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailSetting extends Model
{
    protected $table = 'mship_email_settings';

    protected $fillable = [
        'account_id',
        'email_type',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    public function scopeForType($query, EmailType $type)
    {
        return $query->where('email_type', $type->value);
    }
}
