<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Api\Account
 *
 * @property int $id
 * @property string $name
 * @property string $api_token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Api\Account whereApiToken($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Api\Account whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Api\Account whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Api\Account whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Api\Account whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Api\Account whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Account extends Model
{
    protected $table = 'api_account';
    public $fillable = ['name', 'api_token'];
}
