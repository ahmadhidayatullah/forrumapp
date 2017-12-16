<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
   protected $table = 'threads';

  protected $fillable = [
      'image','description', 'street', 'lat','long','status','category_id'
  ];

  public function member()
  {
    return $this->belongsTo('App\Models\Member');
  }

  public function category()
  {
    return $this->belongsTo('App\Models\Category');
  }

  public function comments()
  {
    return $this->hasMany('App\Models\Comment');
  }
}
