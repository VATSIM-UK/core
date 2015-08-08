<?php

namespace Models\Sso;

use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

class Email extends \Eloquent {

    use SoftDeletingTrait;

    protected $table = "sso_email";
    protected $primaryKey = "sso_email_id";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $hidden = ['sso_email_id'];

    public function email(){
        return $this->belongsTo("\Models\Mship\Account\Email", "account_email_id", "account_email_id");
    }

    public function ssoAccount(){
        return $this->belongsTo("\Models\Sso\Account", "sso_account_id", "sso_account_id");
    }
}
