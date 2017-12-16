<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $guarded = ['id'];

    public function member()
    {
      return $this->belongsTo('App\Models\Member');
    }

    public function report()
    {
      return $this->belongsTo('App\Models\Report');
    }
}
