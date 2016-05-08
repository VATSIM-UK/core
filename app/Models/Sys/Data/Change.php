<?php

namespace App\Models\Sys\Data;

/**
 * App\Models\Sys\Data\Change
 *
 * @property integer $data_change_id
 * @property integer $model_id
 * @property string $model_type
 * @property string $data_key
 * @property string $data_old
 * @property string $data_new
 * @property boolean $automatic
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Sys\Data\Change $model
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Data\Change whereDataChangeId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Data\Change whereModelId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Data\Change whereModelType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Data\Change whereDataKey($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Data\Change whereDataOld($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Data\Change whereDataNew($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Data\Change whereAutomatic($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Data\Change whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Data\Change whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Change extends \App\Models\aModel {
        protected $table = "sys_data_change";
        protected $primaryKey = "data_change_id";
        protected $hidden = ['data_change_id'];

        public function model(){
            return $this->morphTo();
        }
}
