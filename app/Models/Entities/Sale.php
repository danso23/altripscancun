<?php

namespace App\Models\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'sales';
    public $timestamps = true;
    protected $guarded = [];

    /* relations */

    // public function reservation()
    // {
    //     return $this->hasMany('App\Models\Entities\Reservation','sale_id');
    // }

    // public function client()
    // {
    //     return $this->belongsTo('App\Models\Entities\Client');
    // }


    public function getStatusAttribute($value)
    {
        if($value==1){
          return "pagado";
        }elseif($value==2){
          return "pendiente";
        }
    }
}

