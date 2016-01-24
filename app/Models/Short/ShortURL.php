<?php namespace App\Models\Short;

use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

/**
 * App\Models\Short\ShortURL
 *
 * @property integer $id
 * @property string $url
 * @property string $forward_url
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 */
class ShortURL extends \App\Models\aModel
{
    use SoftDeletingTrait;

    protected $table = 'short_url';
    protected $primaryKey = 'id';
}
