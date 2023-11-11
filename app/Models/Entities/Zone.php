<?php

namespace App\Models\Entities;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model{
    
    // use SoftDeletes;

    // protected $dates = ['deleted_at'];
    protected $table = 'zones';
    public $timestamps = true;
    protected $guarded = [];
    /* relations */

    public function hotel(){
      return $this->hasMany('App\Models\Entities\Hotel');
    }

    // public function shuttle_rate(){
    //   return $this->hasMany('App\Entities\Core\ShuttleRate');
    // } 

    // public function shuttle_reservation(){
    //   return $this->hasMany('App\Entities\Core\ShuttleReservation');
    // }
}
