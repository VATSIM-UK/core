<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    protected $table = "api_request";
    public $fillable = [
        "api_account_id",
        "method",
        "url_name",
        "url_full",
        "response_code",
        "response_full",
    ];
}
