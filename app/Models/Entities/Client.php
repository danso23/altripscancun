<?php

namespace App\Models\Entities;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Client extends Model
{
  use SoftDeletes;
  protected $dates = ['deleted_at'];
  protected $table = 'clients';
  public $timestamps = true;
  protected $guarded = [];

  /* relations */

  public function sale()
  {
      return $this->hasMany('App\Models\Entities\Sale','client_id');
  }
}
