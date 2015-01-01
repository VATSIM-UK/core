<?php

namespace Models\Sys\Postmaster;

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Template extends \Models\aModel {

	use SoftDeletingTrait;

        protected $table = "sys_postmaster_template";
        protected $primaryKey = "postmaster_template_id";
        protected $dates = ['created_at', 'updated_at', 'deleted_at'];
}
