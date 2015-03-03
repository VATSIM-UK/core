<?php

namespace Models\Teamspeak;

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Confirmation extends \Models\aModel {

    use SoftDeletingTrait;

    protected $table = 'teamspeak_confirmation';
    protected $primaryKey = 'id';
	protected $fillable = ['*'];


}