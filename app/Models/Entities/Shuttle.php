<?php

namespace App\Models\Entities;

use Illuminate\Database\Eloquent\Model;

class Shuttle extends Model
{
    // protected $dates = ['deleted_at'];
    protected $table = 'shuttle_rates';
    public $timestamps = true;
    protected $guarded = [];

    /* relations  JPJ*/
    // public function zone(){
    //   return $this->belongsTo('App\Entities\Core\Zone');
    // }
}
