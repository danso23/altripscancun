<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    protected $table = 'hotels';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'desc_hotel', 'zone_id', 'is_tour'];
    public $timestamps = false;
}
