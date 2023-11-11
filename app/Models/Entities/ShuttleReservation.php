<?php

namespace App\Models\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Entities\Reservation;
use Carbon\Carbon;

class ShuttleReservation extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'shuttles_reservations';
    public $timestamps = true;
    protected $guarded = [];

    public function setArrivalDateAttribute($value)
    {
        $this->attributes['arrival_date'] = Carbon::parse($value)->format('d-F-Y');
    }

    public function setDepartureDateAttribute($value)
    {
        $this->attributes['departure_date'] = Carbon::parse($value)->format('d-F-Y');
    }


    /* relations */

    public function reservation()
    {
        return $this->morphOne(Reservation::class, 'reservationtable');
    }

    public function zone()
    {
        return $this->belongsTo('App\Models\Entities\Zone');
    }
}
