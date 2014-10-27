<?php

namespace Models\Sys\Postmaster;

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Email extends \Eloquent implements UserInterface, RemindableInterface {

	use UserTrait, RemindableTrait, SoftDeletingTrait;
        protected $table = "sys_postmaster_email";
        protected $primaryKey = "postmaster_email_id";
        protected $dates = ['created_at', 'updated_at', 'deleted_at'];
        protected $hidden = ['postmaster_email_id'];
}
