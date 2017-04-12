<?php

namespace App\Models\Mship\Account;

use App\Traits\RecordsActivity;

/**
 * App\Models\Mship\Account\Note
 *
 * @property int $id
 * @property int $note_type_id
 * @property int $account_id
 * @property int $writer_id
 * @property int $attachment_id
 * @property string $attachment_type
 * @property string $content
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Mship\Account $account
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $attachment
 * @property-read \App\Models\Mship\Note\Type $type
 * @property-read \App\Models\Mship\Account $writer
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note whereAccountId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note whereAttachmentId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note whereAttachmentType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note whereContent($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note whereNoteTypeId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note whereWriterId($value)
 * @mixin \Eloquent
 */
class Note extends \App\Models\Model
{
    use RecordsActivity;

    protected $table = 'mship_account_note';
    protected $primaryKey = 'id';
    protected $dates = ['created_at', 'updated_at'];
    protected $touches = ['account'];

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
