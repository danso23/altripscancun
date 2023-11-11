<?php

namespace App\Models\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'reservations';
    public $timestamps = true;
    protected $guarded = [];

    /* relations */

    public function sale()
    {
        return $this->belongsTo('App\Models\Entities\Sale');
    }

    public function reservationtable()
    {
        return $this->morphTo('reservationtable');
    }

}
