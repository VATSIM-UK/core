<?php

namespace App\Models\Mship\Note;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

/**
 * App\Models\Mship\Note\Type.
 *
 * @property int $id
 * @property string $name
 * @property string|null $short_code
 * @property int $is_available
 * @property int $is_system
 * @property int $is_default
 * @property string $colour_code
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Account\Note[] $notes
 *
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Note\Type isAvailable()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Note\Type isDefault()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Note\Type isShortCode($shortCode)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Note\Type isSystem()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Note\Type onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Note\Type usable()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Note\Type whereColourCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Note\Type whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Note\Type whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Note\Type whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Note\Type whereIsAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Note\Type whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Note\Type whereIsSystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Note\Type whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Note\Type whereShortCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Note\Type whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Note\Type withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Note\Type withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Type extends Model
{
    use SoftDeletingTrait;

    protected $table = 'mship_note_type';

    protected $primaryKey = 'id';

    protected $dates = ['created_at', 'deleted_at'];

    protected $fillable = ['name', 'short_code', 'is_available', 'is_default'];

    public static function getNoteColourCodes()
    {
        return [
            'success' => 'Success (Green)',
            'danger' => 'Danger (Red)',
            'warning' => 'Warning (Orange)',
            'info' => 'Grey',
            'primary' => 'Light Blue',
        ];
    }

    public static function findDefault()
    {
        return self::isDefault()->first();
    }

    public static function scopeIsAvailable($query)
    {
        return $query->whereIsAvailable(1);
    }

    public static function scopeIsSystem($query)
    {
        return $query->whereIsSystem(1);
    }

    public static function scopeIsDefault($query)
    {
        return $query->whereIsDefault(1);
    }

    public static function scopeUsable($query)
    {
        return $query->whereIsAvailable(1)->whereIsSystem(0);
    }

    public static function scopeIsShortCode($query, $shortCode)
    {
        return $query->whereShortCode($shortCode);
    }

    public function notes()
    {
        return $this->hasMany(\App\Models\Mship\Account\Note::class, 'note_type_id', 'id');
    }

    public function save(array $options = [])
    {
        $oldDefault = self::findDefault();
        if ($oldDefault && $oldDefault->exists && $this->is_default && $oldDefault->id != $this->id) {
            $oldDefault->is_default = 0;
            $oldDefault->save();
        }

        return parent::save($options);
    }
}
