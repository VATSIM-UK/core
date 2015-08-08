<?php namespace Models\Staff;

class Attribute extends \Models\aModel
{
    protected $table = 'staff_attributes';
    protected $primaryKey = 'id';

    public function positions()
    {
        return $this->belongsToMany('\Models\Staff\Position', 'staff_attribute_position');
    }
}
