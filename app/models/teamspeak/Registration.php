<?php

namespace Models\Teamspeak;

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Registration extends \Models\aModel {

    use SoftDeletingTrait;

    protected $table = 'teamspeak_registration';
    protected $primaryKey = 'id';
	protected $fillable = ['*'];


}