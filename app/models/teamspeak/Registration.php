<?php

namespace Models\Teamspeak;

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Registration extends \Models\aModel {

    use SoftDeletingTrait;

    protected $table = 'teamspeak_registration';
    protected $primaryKey = 'id';
	protected $fillable = ['*'];
    protected $attributes = ['registration_ip' => '127.0.0.1'];

    public function confirmation() {
        return $this->hasOne("\Models\Teamspeak\Confirmation", "registration_id", "id");
    }

    public function setRegistrationIpAttribute($value) {
        $this->attributes['registration_ip'] = ip2long($value);
    }

    public function getRegistrationIpAttribute() {
        return long2ip($this->attributes['registration_ip']);
    }

}