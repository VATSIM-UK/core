<?php

namespace App\Models\TVCP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitTransferApplication extends Model
{
    use HasFactory;

    protected $table = 'tvcp_visit_transfer_applications';

    public const TYPE_TRANSFER = 'transfer';

    public const TYPE_VISIT = 'visit';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_WITHDRAWN = 'withdrawn';

    public const STATUSES = [
        self::STATUS_SUBMITTED,
        self::STATUS_ACCEPTED,
        self::STATUS_COMPLETED,
        self::STATUS_FAILED,
        self::STATUS_WITHDRAWN,
    ];
}
