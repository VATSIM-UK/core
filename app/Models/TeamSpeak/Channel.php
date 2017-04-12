<?php

namespace App\Models\TeamSpeak;

use App\Models\Model as Model;

/**
 * App\Models\TeamSpeak\Channel
 *
 * @property int $id
 * @property int $parent_id
 * @property string $name
 * @property bool $protected
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TeamSpeak\Channel[] $children
 * @property-read \App\Models\TeamSpeak\Channel $parent
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\Channel whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\Channel whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\Channel whereParentId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\Channel whereProtected($value)
 * @mixin \Eloquent
 */
class Channel extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $table = 'teamspeak_channel';
    protected $primaryKey = 'id';
    protected $guarded = [];

    /**
     * The parent of the current channel.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }

    /**
     * The children (sub-channels) of the current channel.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    /**
     * Accessor for channel protection inheritance. If the database field is null, protection is inherited.
     *
     * @param bool|null $value
     * @return bool
     */
    public function getProtectedAttribute($value)
    {
        if (is_null($value) && !is_null($this->parent)) {
            return $this->parent->protected;
        } else {
            if (is_null($value) && is_null($this->parent)) {
                return false;
            } else {
                return (bool) $value;
            }
        }
    }
}
