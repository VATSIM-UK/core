<?php

namespace App\Models\Sys\Data;

use App\Models\Concerns\OverridesUpdatedAt;
use App\Models\Model;

/**
 * App\Models\Sys\Data\Change.
 *
 * @property int $data_change_id
 * @property int $model_id
 * @property string $model_type
 * @property string $data_key
 * @property string|null $data_old
 * @property string|null $data_new
 * @property \Carbon\Carbon|null $created_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $model
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sys\Data\Change whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sys\Data\Change whereDataChangeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sys\Data\Change whereDataKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sys\Data\Change whereDataNew($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sys\Data\Change whereDataOld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sys\Data\Change whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Sys\Data\Change whereModelType($value)
 *
 * @mixin \Eloquent
 */
class Change extends Model
{
    use OverridesUpdatedAt;

    protected $table = 'sys_data_change';

    protected $primaryKey = 'data_change_id';

    protected $hidden = ['data_change_id'];

    protected $fillable = ['model_type', 'model_id', 'data_key', 'data_old', 'data_new'];

    public function model()
    {
        return $this->morphTo();
    }
}
