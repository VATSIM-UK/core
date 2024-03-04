<?php

namespace App\Models\Mship\Account;

use App\Models\Model;

/**
 * App\Models\Mship\Account\Note.
 *
 * @property int $id
 * @property int $note_type_id
 * @property int $account_id
 * @property int|null $writer_id
 * @property int|null $attachment_id
 * @property string|null $attachment_type
 * @property string $content
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\Mship\Account $account
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $attachment
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 * @property-read \App\Models\Mship\Note\Type $type
 * @property-read \App\Models\Mship\Account|null $writer
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account\Note whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account\Note whereAttachmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account\Note whereAttachmentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account\Note whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account\Note whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account\Note whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account\Note whereNoteTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account\Note whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account\Note whereWriterId($value)
 *
 * @mixin \Eloquent
 */
class Note extends Model
{
    protected $table = 'mship_account_note';

    protected $primaryKey = 'id';

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $touches = ['account'];

    protected $fillable = ['content'];

    protected $trackedEvents = ['created', 'updated', 'deleted'];

    public function account()
    {
        return $this->belongsTo(\App\Models\Mship\Account::class, 'account_id');
    }

    public function writer()
    {
        return $this->belongsTo(\App\Models\Mship\Account::class, 'writer_id');
    }

    public function type()
    {
        return $this->belongsTo(\App\Models\Mship\Note\Type::class, 'note_type_id', 'id');
    }

    public function attachment()
    {
        return $this->morphTo();
    }
}
