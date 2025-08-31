<?php

namespace App\Models\Training\WaitingList;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaitingListRetentionCheck extends Model
{
    use HasFactory;

    protected $table = 'training_waiting_list_retention_checks';

    const STATUS_PENDING = 'pending';

    const STATUS_USED = 'used';

    const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'waiting_list_account_id',
        'token',
        'expires_at',
        'response_at',
        'status',
        'email_sent_at',
        'removal_actioned_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'response_at' => 'datetime',
            'email_sent_at' => 'datetime',
            'removal_actioned_at' => 'datetime',
        ];
    }

    protected function statusHuman(): Attribute
    {
        return Attribute::make(
            get: function ($value, array $attributes) {
                $status = $attributes['status'] ?? null;

                return match ($status) {
                    self::STATUS_USED => 'responded',
                    default => $status,
                };
            }
        );
    }

    public function waitingListAccount()
    {
        return $this->belongsTo(WaitingListAccount::class, 'waiting_list_account_id');
    }
}
