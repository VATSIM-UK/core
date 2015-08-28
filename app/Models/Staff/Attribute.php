<?php namespace App\Models\Staff;

class Attribute extends \App\Models\aModel
{
    protected $table = 'staff_attributes';
    protected $primaryKey = 'id';

    public function positions()
    {
        return $this->belongsToMany('\App\Models\Staff\Position', 'staff_attribute_position');
    }
}
