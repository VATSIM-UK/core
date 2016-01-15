<?php

namespace App\Models\Mship\Note;

use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;
use Carbon\Carbon;

/**
 * App\Models\Mship\Note\Type
 *
 * @property integer $note_type_id
 * @property string $name
 * @property string $short_code
 * @property boolean $is_available
 * @property boolean $is_system
 * @property boolean $is_default
 * @property string $colour_code
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Account\Note[] $notes
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Note\Type isAvailable()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Note\Type isSystem()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Note\Type usable()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Note\Type isShortCode($shortCode)
 */
class Type extends \Eloquent {
	use SoftDeletingTrait;
        protected $table = "mship_note_type";
        protected $primaryKey = "note_type_id";
        protected $dates = ['created_at', 'deleted_at'];

        public static function scopeIsAvailable($query){
            return $query->whereIsAvailable(1);
        }

        public static function scopeIsSystem($query){
            return $query->whereIsSystem(1);
        }

        public static function scopeUsable($query){
            return $query->whereIsAvailable(1)->whereIsSystem(0);
        }

        public static function scopeIsShortCode($query, $shortCode){
            return $query->whereShortCode($shortCode);
        }

        public function notes(){
            return $this->hasMany("\App\Models\Mship\Account\Note", "note_type_id", "note_type_id");
        }
}
