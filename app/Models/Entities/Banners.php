<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Banners extends Model
{
    //use SoftDeletes;
  
  protected $table = 'banners';
  public $timestamps = true;
  protected $guarded = [];

  /* relations */

  public function zones(){
    
    return $this->belongsTo('App\Entities\Core\Zone', 'zone', 'id');
    //return $this->hasMany('App\Entities\Zone', 'id_zone', 'id');
    
  }
}
