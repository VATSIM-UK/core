<?php

namespace App\Models\Mship\Account;

use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

/**
 * App\Models\Mship\Account\Note
 *
 * @property integer $account_note_id
 * @property integer $note_type_id
 * @property integer $account_id
 * @property integer $writer_id
 * @property string $attachment_type
 * @property integer $attachment_id
 * @property string $content
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property-read \App\Models\Mship\Account $account
 * @property-read \App\Models\Mship\Account $writer
 * @property-read \App\Models\Mship\Note\Type $type
 * @property-read \App\Models\Mship\Account\Note $attachment
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note whereAccountNoteId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note whereNoteTypeId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note whereAccountId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note whereWriterId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note whereAttachmentType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note whereAttachmentId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note whereContent($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note whereDeletedAt($value)
 * @mixin \Eloquent
 */
class Note extends \App\Models\aModel
{

    use SoftDeletingTrait, RecordsActivity;

    protected $table      = "mship_account_note";
    protected $primaryKey = "account_note_id";
    protected $dates      = ['created_at', 'updated_at', 'deleted_at'];
    protected $touches    = ['account'];

    public function account()
    {
        return $this->belongsTo("\App\Models\Mship\Account", "account_id");
    }

    public function writer()
    {
        return $this->belongsTo("\App\Models\Mship\Account", "writer_id");
    }

    public function type()
    {
        return $this->belongsTo("\App\Models\Mship\Note\Type", "note_type_id", "note_type_id");
    }

    public function attachment(){
        return $this->morphTo();
    }
}
