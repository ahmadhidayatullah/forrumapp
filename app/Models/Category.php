<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
  protected $fillable = [
      'name','icon'
  ];

  public function reports()
  {
    return $this->hasMany('App\Models\Report');
  }
}
