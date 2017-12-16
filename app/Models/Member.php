<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Member extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name','username', 'email', 'password','avatar','social_id','social_provider','address','birtday'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public function reports()
    {
      return $this->hasMany('App\Models\Report');
    }

    public function comments()
    {
      return $this->hasMany('App\Models\Comment');
    }

    public function notifications()
    {
      return $this->hasMany('App\Models\Notification');
    }
}
