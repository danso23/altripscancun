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

    public function GetHotelEdit(){
        //MOdif
        $tours  = Hotel::orderBy('name')->where('is_tour', '1')->get(['name','id','sort_number'])->toArray();
        $hotels = Hotel::orderBy('name')->where([ ['is_tour', '0'], ['bActivo', '1'] ])->get(['name','id'])->toArray();
        
        return response()->json([
            'Hotels' => $hotels,
            'Tours' => $tours,
            'regar' => trans('messages.welcome')
        ]);
    }      

    public function GetZonas(){

        $Zona = Zona::get(['name','id'])->toArray();

        if(!empty($Zona)){
            return response()->json($Zona);
        }

    }

    public function DeleteItemHotel(Request $request){

        $idHotel = (isset($request->id) && !is_null($request->id) && !empty($request->id)) ? $request->id : "";

        if($idHotel == ""){
            return response()->json([
                'lEstatus' => true,
                'cEstatus' => "Please select a Identification of Hotel, Try again."
            ]);
        }

        try{

            $Hotel = Hotel::where('id', $idHotel) ->update(['bActivo' => false]);
            // $Hotel['bActivo'] = 0;

            if($Hotel){
                return response()->json([

                    'lEstatus' => false,
                    'cEstatus' => 'Registro eliminado correctamente'
                ],200);
            }            
        }
        catch(\Illuminate\Database\QueryException $ex){
            
            return response()->json([
                'lEstatus' => true,
                'cEstatus' => $ex->getMessage()
            ]);
        }            
    }  

    public function UpdateItemHotel(Request $request){
        
        $idHotel = (isset($request->id) && !is_null($request->id) && !empty($request->id)) ? $request->id : "";
        $NameHotel = (isset($request->name) && !is_null($request->name) && !empty($request->name)) ? $request->name : "";
        $ZoneHotel = (isset($request->zone_id) && !is_null($request->zone_id) && !empty($request->zone_id)) ? $request->zone_id : "";

        if($idHotel == ""){
            return response()->json([
                'lEstatus' => true,
                'cEstatus' => "Please select a Identification of Hotel, Try again."
            ]);
        }

        if($NameHotel == ""){
            return response()->json([
                'lEstatus' => true,
                'cEstatus' => "Please need a name Hotel, Try again."
            ]);
        }

        if($ZoneHotel == ""){
            return response()->json([
                'lEstatus' => true,
                'cEstatus' => "Please need a Zone, Try again."
            ]);
        }

        $Update = [
            'name' => $NameHotel,
            'zone_id' =>$ZoneHotel
        ];


        try{

            $Hotel = Hotel::where('id', $idHotel) ->update($Update);            

            if($Hotel){
                return response()->json([
                    'lEstatus' => false,
                    'cEstatus' => 'Register update success'
                ],200);
            }            
        }
        catch(\Illuminate\Database\QueryException $ex){
            
            return response()->json([
                'lEstatus' => true,
                'cEstatus' => $ex->getMessage()
            ]);
        } 
    }
}
