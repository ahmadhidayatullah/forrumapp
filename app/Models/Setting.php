<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
  protected $fillable = [
      'app_name','description', 'instansi', 'website','lokasi','logo'
  ];
}
