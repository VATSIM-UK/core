<?php

namespace App\Modules\Statistics\Models;

use Illuminate\Database\Eloquent\Model;

class Atc extends Model {
    use SoftDeletes;

    protected $table = "statistics_atc";
    protected $primaryKey = "id";
    protected $fillable = ["callsign", "connected_at", "disconnected_at"];
    public $dates = ["connected_at", "disocnnected_at", "created_at", "updated_at", "deleted_at"];
    public $timestamps = true;

}