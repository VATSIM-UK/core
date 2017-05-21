<?php

namespace App\Models\Mship\Account\Note;

use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

/**
 * App\Models\Mship\Account\Note\Note
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
 * @property-read \App\Models\Mship\Account $actioner
 * @property-read \App\Models\Mship\Account\Note\Flag $flag
 * @property-read \App\Models\Mship\Account\Note\Format $format
 * @property-write mixed $data
 * @property-read \App\Models\Mship\Account $user
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note\Note whereAccountId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note\Note whereAttachmentId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note\Note whereAttachmentType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note\Note whereContent($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note\Note whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note\Note whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note\Note whereNoteTypeId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note\Note whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note\Note whereWriterId($value)
 * @mixin \Eloquent
 */
class Note extends \Eloquent
{
    use SoftDeletingTrait, RecordsActivity;

    protected $table = 'mship_account_note';
    protected $primaryKey = 'id';
    protected $dates = ['created_at', 'deleted_at'];
    protected $hidden = ['id'];

    public function user()
    {
        return $this->belongsTo(\App\Models\Mship\Account::class, 'account_id');
    }

    public function actioner()
    {
        return $this->belongsTo(\App\Models\Mship\Account::class, 'actioner_id');
    }

    public function flag()
    {
        return $this->hasOne(\App\Models\Mship\Account\Note\Flag::class, 'account_note_id', 'id');
    }

    public function format()
    {
        return $this->hasOne(\App\Models\Mship\Account\Note\Format::class, 'account_note_id', 'id');
    }

    public function setDataAttribute($value)
    {
        $this->attributes['data'] = serialize($value);
    }
}
