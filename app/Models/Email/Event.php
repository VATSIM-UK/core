<?php

namespace App\Models\Email;

use App\Models\Model;

/**
 * App\Models\Email\Event
 *
 * @property int $id
 * @property string $broker
 * @property string|null $message_id
 * @property string $name
 * @property string $recipient
 * @property array $data
 * @property \Carbon\Carbon|null $triggered_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Email\Event whereBroker($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Email\Event whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Email\Event whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Email\Event whereMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Email\Event whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Email\Event whereRecipient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Email\Event whereTriggeredAt($value)
 * @mixin \Eloquent
 */
class Event extends Model
{
    protected $table = 'email_events';
    protected $dates = ['triggered_at'];
    protected $guarded = ['id'];
    protected $casts = [
        'data' => 'array',
    ];
    public $timestamps = false;
}
