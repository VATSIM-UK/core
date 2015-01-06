<?php

namespace Models\Sys;

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Setting extends \Eloquent implements UserInterface, RemindableInterface {

	use UserTrait, RemindableTrait, SoftDeletingTrait;
        protected $table = "sys_setting";
        protected $primaryKey = "setting_id";
        protected $dates = ['created_at', 'updated_at', 'deleted_at'];
        protected $hidden = ['setting_id'];
}
