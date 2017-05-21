<?php

namespace App\Models\Sys;

use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

/**
 * App\Models\Sys\Read
 *
 * @property int $id
 * @property int $notification_id
 * @property int $account_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Read active()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Read general()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Read important()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Read mustAcknowledge()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Read operational()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Read since($sinceTimestamp)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Read user()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Read whereAccountId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Read whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Read whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Read whereNotificationId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Read whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Read withStatus($status)
 * @mixin \Eloquent
 */
class Read extends \App\Models\Model
{
    use SoftDeletingTrait;

    protected $table = 'sys_notification_read';
    protected $primaryKey = 'id';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $hidden = ['id'];

    const STATUS_MUST_ACKNOWLEDGE = 99; // Will interrupt login process AND ban from services until acknowledged.
    const STATUS_IMPORTANT = 70; // Will interrupt login process.
    const STATUS_OPERATIONAL = 50; // Web services
    const STATUS_GENERAL = 30; // General messages, to be read at some point.
    const STATUS_USER = 10; // User specific
    const STATUS_UNPUBLISHED = 0; // Drafts.

    public function scopeActive($query)
    {
        return $query->where('status', '!=', self::STATUS_UNPUBLISHED);
    }

    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', '=', $status);
    }

    public function scopeMustAcknowledge($query)
    {
        return $query->withStatus(self::STATUS_MUST_ACKNOWLEDGE);
    }

    public function scopeImportant($query)
    {
        return $query->withStatus(self::STATUS_IMPORTANT);
    }

    public function scopeOperational($query)
    {
        return $query->withStatus(self::STATUS_OPERATIONAL);
    }

    public function scopeGeneral($query)
    {
        return $query->withStatus(self::STATUS_GENERAL);
    }

    public function scopeUser($query)
    {
        return $query->withStatus(self::STATUS_USER);
    }

    public function scopeSince($query, $sinceTimestamp)
    {
        if (!($sinceTimestamp instanceof \Carbon\Carbon)) {
            $sinceTimestamp = \Carbon\Carbon::parse($sinceTimestamp);
        }

        return $query->where('effective_at', '>=', $sinceTimestamp);
    }
}
