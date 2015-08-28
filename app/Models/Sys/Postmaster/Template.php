<?php

namespace App\Models\Sys\Postmaster;

use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

class Template extends \Models\aModel {

    use SoftDeletingTrait;

    const PRIORITY_LOW = 10;
    const PRIORITY_MED = 50;
    const PRIORITY_HIGH = 70;
    const PRIORITY_NOW = 90;

    protected $table = "sys_postmaster_template";
    protected $primaryKey = "postmaster_template_id";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    public static function findFromKey($key){
        return Template::where(\DB::raw("CONCAT(`section`, '_', `area`, '_', `action`)"), "=", $key)->first();
    }

    public function queuedEmails() {
        return $this->belongsTo("\App\Models\Sys\Postmaster\Queue", "postmaster_template_id", "postmaster_template_id");
    }

    public function getDisplayValueAttribute() {
        return array_get($this->attributes, "section")."_".array_get($this->attributes, "area")."_".array_get($this->attributes, "action");
    }

}
