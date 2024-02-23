<?php

namespace App\Http\Controllers;

use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Entities\Hotel as Hotel;
use App\Models\Entities\Banners as Banner;
use App\Models\Entities\Zone as Zona;
use App\Models\Entities\ShuttleReservation as Shuttle;
use App\Models\Entities\Reservation as Reservation;
use App\Models\Entities\Client as Client;
use App\Models\Entities\Sale as Sale;

use \Illuminate\Support\Facades\Lang;
class GetCatalogos extends Controller
{    
    
    public function getCatalogos(){

        $tours  = Hotel::orderBy('name')->where('is_tour', '1')->get(['name','id','sort_number'])->toArray();
        $hotels = Hotel::orderBy('name')->where([ ['is_tour', '0'], ['bActivo', '1'] ])->get(['name','id'])->toArray();

        return response()->json([
            'Hotels' => $hotels,
            'Tours' => $tours,
            'regar' => trans('messages.welcome')
        ]);
    }

    //Function Admin
    public function GetHotelEdit(){

        $tours  = Hotel::orderBy('name')->where('is_tour', '1')->get(['name','id','sort_number'])->toArray();
        $hotels = Hotel::orderBy('name')->where([ ['is_tour', '0'], ['bActivo', '1'] ])->get(['name','zone_id','id'])->toArray();

        return response()->json([
            'Hotels' => $hotels,
            'Tours' => $tours
        ]);
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
                'cEstatus' => "Please select Hotel, Try again."
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

    public function GetZonas(){

        $Zona = Zona::get(['name','id'])->toArray();

        if(!empty($Zona)){
            return response()->json($Zona);
        }
    }

    public function DeleteItemZones(Request $request){

        $idZone = (isset($request->id) && !is_null($request->id) && !empty($request->id)) ? $request->id : "";

        $bDelete = (isset($request->bSi) && !is_null($request->bSi) && !empty($request->bSi)) ? $request->bSi : "";



        if($idZone == ""){
            return response()->json([
                'lEstatus' => true,
                'cEstatus' => "Please select a Identification of Hotel, Try again."
            ]);
        }

        if(!$bDelete){

            // dd("s");
            $Hotel = Hotel::where('zone_id', $idZone)->get()->toArray();//->update(['bActivo' => false]);

            if(!empty($Hotel)){
                return response()->json([
                    'lEstatus' => true,
                    'cEstatus' => "Caution! Exist Hotel's with this zone."
                ]);
            }
        }
        
        //dd($bDelete);

        try{

            $Hotel = Hotel::where('id', $idHotel) ->update(['bActivo' => false]);
            // $Hotel['bActivo'] = 0;

            if($Hotel){
                return response()->json([

                    'lEstatus' => false,
                    'cEstatus' => 'Item delete success'
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


    public function GetReservation(){

        $Query  =    DB::table('shuttles_reservations as sr')
                        ->join('reservations as r','r.reservationtable_id','=','sr.id')
                        ->join('sales as s','s.id','=','r.sale_id')
                        ->join('clients as c','c.id','=','s.client_id')
                        // ->join('zones as z','z.id','=','sr.zone_id')//  %Y/%m/%d
                        ->select('c.name as Nombre','s.id as FolioVenta',
                                  's.code as FolioReservacion',
                                  'sr.id as FolioShuRese',
                                  DB::raw('DATE_FORMAT(sr.created_at,"%d/%m/%Y") as FechaReservacion'),
                                 'sr.arrival_date as FechaLlegada','sr.arrival_time as HraLLegada',
                                 'sr.departure_date as FechaSalida','sr.departure_time as HoraPartida',
                                 //'rever.id as FolioReservacion',
                                 'sr.pax as NumeroPasajeros',
                                 'sr.arrival_airline as AerolineaLlegada',
                                 'sr.departure_airline as AerolineaSalida',
                                 'sr.arrival_destination as Hotel',
                                 's.payment_type as TipoPago','s.total as CostoTotal'
                        )  
                        ->orderBy('sr.created_at', 'desc');
                        // ,'z.name as Zona'
        $DataReport = $Query->get()->toArray();

        return response()->json([
            'lEstatus' => false,
            'cEstatus' => '',
            'cData' => $DataReport
        ]);
    }

    public function update_travel(Request $request){

        $paramsUpdate = [];
        $iIdRegistro = (isset($request->idreg) && $request->idreg !== '') ? $request->idreg : '';
        $dateLlegada = (isset($request->arrival_date_s) && !empty($request->arrival_date_s)) ? $request->arrival_date_s : '';
        $dateSalida  = (isset($request->departure_date_s) && !empty($request->departure_date_s)) ? $request->departure_date_s : ''; 

        if(empty($iIdRegistro)){
            return response()->json([
                'lEstatus' => true,
                'cEstatus' => 'The ID Reservation field is required'
            ]);
        }

        if(!empty($dateLlegada)){
            $paramsUpdate['arrival_date'] = $dateLlegada;
        }

        if(!empty($dateSalida)){
            $paramsUpdate['departure_date'] = $dateSalida;
        }

        DB::beginTransaction();
        try{

            $Reservation = Shuttle::find($iIdRegistro);
            $sr = Shuttle::where('id',$iIdRegistro)->update($paramsUpdate);

            if($sr){
                DB::commit();
                return response()->json([
                    'lEstatus' => false,
                    'cEstatus' => 'Item update success'
                ]);
            }
            else{

                DB::rollback();

                return response()->json([
                    'lEstatus' => true,
                    'cEstatus' => 'I Have a error an update item, try again'
                ]);
            }
            return response()->json(['data' => $sr]);
        }
        catch(\Illuminate\Database\QueryException $ex){
            DB::rollback();
            return response()->json([
                'lError' => true,
                'cError' => $ex->getMessage()
            ]);
        }
    }
}
