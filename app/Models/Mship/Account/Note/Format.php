<?php

namespace App\Models\Mship\Account\Note;

use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

/**
 * App\Models\Mship\Account\Note\Format
 *
 * @property-read \App\Models\Mship\Account\Note $note
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note\Format onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note\Format withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note\Format withoutTrashed()
 * @mixin \Eloquent
 */
class Format extends \Eloquent
{
    use SoftDeletingTrait;

    protected $table = 'mship_account_note_format';
    protected $primaryKey = 'account_note_format_id';
    protected $dates = ['created_at', 'deleted_at'];
    protected $hidden = ['account_note_format_id'];

    public function note()
    {
        return $this->belongsTo(\App\Models\Mship\Account\Note::class, 'format_id', 'account_note_format_id');
    }
}
