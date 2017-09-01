<?php

namespace App\Models\Short;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

/**
 * App\Models\Short\ShortURL
 *
 * @property int $id
 * @property string $url
 * @property string $forward_url
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Short\ShortURL onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Short\ShortURL whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Short\ShortURL whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Short\ShortURL whereForwardUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Short\ShortURL whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Short\ShortURL whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Short\ShortURL whereUrl($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Short\ShortURL withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Short\ShortURL withoutTrashed()
 * @mixin \Eloquent
 */
class ShortURL extends Model
{
    use SoftDeletingTrait;

    protected $table = 'short_url';
    protected $primaryKey = 'id';
}
