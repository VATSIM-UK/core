<?php

namespace Models\Sys;

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;
use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

class Log extends \Eloquent implements UserInterface, RemindableInterface {

	use UserTrait, RemindableTrait, SoftDeletingTrait;
        protected $table = "sys_log";
        protected $primaryKey = "log_id";
        protected $dates = ['created_at', 'deleted_at'];
        protected $hidden = ['log_id'];
}
