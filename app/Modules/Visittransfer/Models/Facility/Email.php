<?php

namespace App\Modules\Visittransfer\Models\Facility;

use Illuminate\Database\Eloquent\Model;


class Email extends Model
{
  protected $table      = 'vt_facility_email';
  protected $primaryKey = 'id';

  public $timestamps    = true;
  public $fillable      = [
      'facility_id',
      'email',
  ];

  protected $dates   = [
      'created_at',
      'updated_at',
  ];

  public function facility(){
      $this->belongsTo(\App\Modules\Visittransfer\Models\Facility::class);
  }

}
