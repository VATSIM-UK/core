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
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Short\ShortURL whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Short\ShortURL whereUrl($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Short\ShortURL whereForwardUrl($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Short\ShortURL whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Short\ShortURL whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Short\ShortURL whereDeletedAt($value)
 * @mixin \Eloquent
 */
class ShortURL extends \App\Models\aModel
{
    use SoftDeletingTrait;

    protected $table = 'short_url';
    protected $primaryKey = 'id';
}
