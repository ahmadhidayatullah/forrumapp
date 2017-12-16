<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
  protected $fillable = [
      'body','report_id'
  ];

  public function member()
  {
    return $this->belongsTo('App\Models\Member');
  }

  public function report()
  {
    return $this->belongsTo('App\Models\Report');
  }
}
