<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Entities\Hotel as Hotel;
use App\Models\Entities\Banners as Banner;
use App\Models\Entities\Zone as Zona;

use \Illuminate\Support\Facades\Lang;
class GetCatalogos extends Controller
{    
    
    public function getCatalogos(){

        //include in your controller
        //use Lang;

        //in code you get values like
        // Lang::get('messages.error');
        $tours  = Hotel::orderBy('name')->where('is_tour', '1')->get(['name','id','sort_number'])->toArray();
        $hotels = Hotel::orderBy('name')->where([ ['is_tour', '0'], ['bActivo', '1'] ])->get(['name','id'])->toArray();
        
        return response()->json([
            'Hotels' => $hotels,
            'Tours' => $tours,
            'regar' => trans('messages.welcome')
        ]);

        // $banners= Banner::join('shuttle_rates', 'shuttle_rates.zone_id', '=', 'banners.zone')->where('min', '<', 5)->where('state', '=', 1)->get();
        // return view('web.index',compact('hotels'))->with('banners', $banners)->with('tours', $tours);            
    }        
}
