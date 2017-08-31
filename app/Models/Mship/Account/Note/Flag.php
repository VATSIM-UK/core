<?php

namespace App\Models\Mship\Account\Note;

use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

/**
 * App\Models\Mship\Account\Note\Flag
 *
 * @property-read \App\Models\Mship\Account $flagger
 * @property-read \App\Models\Mship\Account\Note $note
 * @property-read \App\Models\Mship\Account $resolver
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note\Flag onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note\Flag withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Note\Flag withoutTrashed()
 * @mixin \Eloquent
 */
class Flag extends \Eloquent
{
    use SoftDeletingTrait;

    protected $table = 'mship_account_note_flag';
    protected $primaryKey = 'account_note_flag_id';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $hidden = ['account_note_flag_id'];

    public function flagger()
    {
        return $this->belongsTo(\App\Models\Mship\Account::class, 'flag_by');
    }

    public function resolver()
    {
        return $this->belongsTo(\App\Models\Mship\Account::class, 'resolve_by');
    }

    public function note()
    {
        return $this->belongsTo(\App\Models\Mship\Account\Note::class, 'flag_id', 'account_note_flag_id');
    }
}
