<?php namespace Models\Short;

use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

class ShortURL extends \Models\aModel
{
    use SoftDeletingTrait;

    protected $table = 'short_url';
    protected $primaryKey = 'id';
}
