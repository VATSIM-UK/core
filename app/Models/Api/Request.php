<?php

namespace App\Models\Api;

use App\Models\Model;

/**
 * App\Models\Api\Request.
 *
 * @property int $id
 * @property int $api_account_id
 * @property string|null $method
 * @property string $url_name
 * @property string $url_full
 * @property int|null $response_code
 * @property string|null $response_full
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Api\Request whereApiAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Api\Request whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Api\Request whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Api\Request whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Api\Request whereResponseCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Api\Request whereResponseFull($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Api\Request whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Api\Request whereUrlFull($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Api\Request whereUrlName($value)
 *
 * @mixin \Eloquent
 */
class Request extends Model
{
    protected $table = 'api_request';

    public $fillable = [
        'api_account_id',
        'method',
        'url_name',
        'url_full',
        'response_code',
        'response_full',
    ];
}
