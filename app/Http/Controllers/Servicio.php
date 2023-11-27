<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Rules\DateVerified;
use App\Models\Entities\Hotel as Hotel;
use App\Models\Entities\Shuttle as Shuttle;
use App\Models\Entities\Sale as Sale;
use App\Models\Entities\Client as Client;
use App\Models\Entities\ShuttleReservation as ShuttleReservation;
use App\Models\Entities\Reservation as Reservation;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class Servicio extends Controller
{
    
    public $Keys = [];
    public $UrlPay = [];
    public $globalTest = "";

    public function __construct() {
        
        $this->Keys = [
            "CLIENT_ID" => env("PAYPAL_CLIENT_ID"),
            "APP_SECRET" => env("PAYPAL_SECRET")
        ];

        $this->UrlPay['UrlPaypal'] = ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1')
                            ? "https://api-m.sandbox.paypal.com"
                            : "https://api-m.paypal.com";

        $this->globalTest = ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1')
                            ? " [DESARROLLO] "
                            : "";

        // $this->UrlPay = [
        //     "sandbox" => "https://api-m.sandbox.paypal.com",
        //     "production" => "https://api-m.paypal.com"
        // ];
    }

    public function getInfoTrip(Request $request){

        
        try{

            // $total = $this->rate($hotel->zone_id, $request->passengers, $request->type_trip);
            /*            
            roud trip = 1
            hotel - airpot = 3
            airpot - hotel = 2            
            */
            $this->validate($request, [
                'type_trip' => 'required|in:1,2,3,4',                
                'hotel_id' => 'required',
                'passengers'=>'required|digits_between:1,10',
                'arrival_date'=> [new DateVerified],
                'departure_date'=> [new DateVerified]
            ]);
            

            $hotel = Hotel::where('id', $request->hotel_id)->first(['name','zone_id']);
            
            // return response()->json([
            //     "error" => $request->hotel_id
            // ]);

            
            if($hotel){
                $total = $this->rate($hotel->zone_id, $request->passengers, $request->type_trip);
            }
            else{
                return response()->json([
                    "lEstatus" => false,
                    "cEstatus" => "No se encontro el hotel ingresado. [Notifique a soporte]"
                ]);
            }
            
            if ($total == 0) {                
                return response()->json([
                    "lEstatus" => false,
                    "cEstatus" => "No existe cuota para este destino."
                ]);
            }            

            // $departure_date     = strtotime($request->departure_date);
            // $new_departure_date = date("d-F-Y", $departure_date);
            
            // $data = array(
            //   'type_trip'     => $request->type_trip,
            //   'place'         => $hotel->name,
            //   'name'          => $this->getTypeTrip($request->type_trip),
            //   'zone_id'       => $hotel->zone_id,
            //   'pax'           => $request->passengers,
            //   'total'         => $total,
            //   'arrival_date'  => $request->arrival_date,
            //   'departure_date'=> $new_departure_date,
            // );        
            
            $jsonReturn = [
                'Hotel' => $hotel['name'],
                'PagoTotal' => $total,
                'type_trip'     => $request->type_trip,
                'place'         => $hotel->name,
                'name'          => $this->getTypeTrip($request->type_trip),
                'zone_id'       => $hotel->zone_id,
                'pax'           => $request->passengers,
                'total'         => $total,
                'arrival_date'  => $this->convertHoraGeneralizada($request->arrival_date),
                'departure_date'=> $this->convertHoraGeneralizada($request->departure_date),
                "bton_paypal" => $this->obtenerBotonPaypal($total,$this->getTypeTrip($request->type_trip),$hotel->name),
                "instance" => $this->instanciaPaypay()
            ];

            return response()->json($jsonReturn);
        }
        catch(\Exception $ex){
                                    
            return response()->json([
                'lError' => true,
                'cError' => $ex->getMessage()
            ]);
        } 
    }

    public function savetrip(Request $request){

        
        $resp_val = $this->validationFormTrip($request->data);
        if(!$resp_val['bEstatus']){
            return response()->json([
                'bError' => true,
                'cMensagge' => $resp_val['cMensaje']
            ]);
        }

        return $bStatus = $this->set_travel($request->data);
        
      // }catch (\Exception $e) {
      //   \Log::warning($e);
      //   return redirect()->back()->withErrors(trans('responses.totaliscero'))->withInput();
      // }
    }

    public function rate($zone_id,$pax,$type_trip){
        
        $total = null;

        try{

            $rateShuttles = Shuttle::where('zone_id', $zone_id)->get();

            if($rateShuttles) {
                foreach ($rateShuttles as $rateShuttle) {
                    if ($pax >= $rateShuttle->min && $pax <= $rateShuttle->max) {
                        if ($type_trip == 1) {
                          return $rateShuttle->roundtripmx;
                        }
                        elseif ($type_trip==2 || $type_trip==3 || $type_trip==4) {
                          return $rateShuttle->onewaymx;
                        }
                    }
                }
            }

            return $total;

        }
        catch(\Exception $ex){

            return false;
        }                        
    }

    public function getTypeTrip($type_trip){      
        // $lg = Session::get('lang');
        $lg = "en";

        if($type_trip == 1) {
            if($lg == "es"){
                return 'Transporación / Viaje Redondo';
            }else{
                return 'Shuttle / Round Trip';
            }
        }
        elseif($type_trip == 2) {
            if($lg == "es"){
                return 'Transportación / Del Aeropuerto';
            }else{
                return 'Shuttle / From Airport';
            }
        }
        elseif($type_trip == 3){
            if($lg == "es"){
                return 'Transportación / Hacia el Aeropuerto';
            }else{
                return 'Shuttel / To Airport';
            }
        }
    }

    function validarCaptcha($captcha){

        
        if ($captcha == '') {
            return false;
        }
        else {
            $obj = new \stdClass();
            $obj->secret = env('KEYCAPTCHA_SECRET');
            $obj->response = $captcha;
            $obj->remoteip = $_SERVER['REMOTE_ADDR'];
            $url = 'https://www.google.com/recaptcha/api/siteverify';

            $options = [
                    'http'  => [
                                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                                'method'  => 'POST',
                                'content' => http_build_query($obj)
                    ]
                ];
            


            $context = stream_context_create($options);
            $result  = file_get_contents($url, false, $context);
            
            $validar = json_decode($result);
            
            return ($validar->success) ? true : false;
        }
    }

    function vistaPaypal(){
        return view('paypal');
    }

    function vistaMail(){
        return view('examplemail');
    }

    public function generateAccessToken(){
                     
        try{   
            $url  = $this->UrlPay["UrlPaypal"]."/v1/oauth2/token";
            $keyClien = $this->Keys['CLIENT_ID'];
            $data = "grant_type=client_credentials";        
            
            $header = [
                "User-Agent" => "allTripsCancun",
                'Authorization' => "Basic ".base64_encode($this->Keys["CLIENT_ID"].":".$this->Keys["APP_SECRET"]),            
                "Accept" => "*/*"
            ];  
                    
            $ret = [];
            foreach($header as $item => $value) {
                $ret[] = "$item: $value";
            }            

            $respuesta = $this->GetCurl($url,true,$data,$ret);                
            return json_decode($respuesta,true)["access_token"];
        }
        catch(\Exception $ex){
            
            return response()->json([
                'lError' => true,
                'cError' => $ex->getMessage()
            ]);
        }
    }

    public function ConfirmarPago(Request $request){

        /*"firstName": "",
        "hotel_s": "Agua Eco Design Hotel Tulum",
        "total_s": "140.00",
        "passenger_s": "2",
        "lastName": "",
        "email": "",
        "phone": "",
        "airbnb": "",
        "airlineArrival": "",
        "flightNumberArrival": "",
        "arrivalHourArrival": "",
        "airlineDeparture": "",
        "flightNumberDeparture": "",
        "arrivalHourDeparture": "",
        "submit": "" */        
        $resp_val = $this->validationFormTrip($request->data);
        if(!$resp_val['bEstatus']){
            return response()->json([
                'bError' => true,
                'cMensagge' => $resp_val['cMensaje']
            ]);
        }

        $token = $this->generateAccessToken();        
        $url   = $this->UrlPay["UrlPaypal"]."/v2/checkout/orders/".$request->orderID."/capture";

        $header = [
            "User-Agent" => "allTripsCancun",
            'Authorization' => "Bearer ".$token,
            "Accept" => "*/*",
            "content-type" => "application/json"
        ];    
        
        $ret = [];
        foreach($header as $item => $value) {
            $ret[] = "$item: $value";
        } 
        
        $datos = "";
        

        $respuesta = $this->GetCurl($url,true,$datos,$ret);
        $respuesta = json_decode($respuesta,true);        
        
        if($respuesta['status'] == "COMPLETED"){ 
            $respuesta = array_merge($respuesta['purchase_units'][0], $request->data);
            return $bStatus = $this->set_travel($respuesta);    
        }                
    }

    public function set_travel($dataPaypal){
        
        // return response()->json($dataPaypal);

        try{
            
            $reservation_code = Sale::orderBy('id', 'DESC')->first(['id']);

            if($reservation_code){
              $reservation_code = "ATC-".((int)$reservation_code->id+1);
            }
            else{
              $reservation_code = "ATC-1";
            }

            $client = Client::create([
                'name'  => $dataPaypal['firstName']." ".$dataPaypal['lastName'],
                'email' => $dataPaypal['email'],
                'phone' => $dataPaypal['phone']
            ]); 

            $sale = Sale::create([
                'status'=>2,
                'payment_type'=> (isset($dataPaypal['payments'])) ? 'PAYPAL' : 'CASH',
                'total'=>$dataPaypal['total_s'],
                'code'=>$reservation_code,
                'client_id'=>$client->id
            ]);            

            $sr = ShuttleReservation::create([
                'type_trip'               => $dataPaypal['type_trip_s'],
                'pax'                     => $dataPaypal['passenger_s'],
                'arrival_date'            => $dataPaypal["arrival_date_s"],
                'arrival_airline'         => $dataPaypal['airlineArrival'],
                'arrival_flight'          => $dataPaypal['flightNumberArrival'],
                'arrival_time'            => $dataPaypal['arrivalHourArrival'],
                'arrival_pickup'          => $dataPaypal['airbnb'],
                'departure_date'          => $dataPaypal['departure_date_s'],
                'departure_airline'       => $dataPaypal['airlineDeparture'],
                'departure_flight'        => $dataPaypal['flightNumberDeparture'],
                'departure_time'          => $dataPaypal['arrivalHourDeparture'],
                'departure_pickup'        => $dataPaypal['hotel_s'],
                'arrival_destination'     => $dataPaypal['hotel_s'],
                // 'departure_pickup_time'   =>$request->departure_pickup_time.':'.$request->departure_pickup_time,
                'departure_destination'   => 'Aiport Cancún',
                'zone_id'                 => $dataPaypal['zone_s'],
                'special_request'         => ""
            ]);

            $save = $sr->reservation()->updateOrCreate([
                'reservationtable_id'     => $sr->id,
                'reservationtable_type'   => ShuttleReservation::class,
              ],
              [
                'sale_id'  => $sale->id,
                'subtotal' => $dataPaypal['total_s'],
                //'comments' => $request->comments,
              ]
            );

            $dataPaypal['reservation_code'] = $reservation_code;
            $dataPaypal['payment_type'] = (isset($dataPaypal['payments'])) ? 'PAYPAL' : 'CASH';
            $dataSend = $dataPaypal;

            unset($dataSend['reference_id']);
            unset($dataSend['shipping']);
            unset($dataSend['payments']);

            if($save){
                
                $this->EnviarCorreo($dataSend);                

                return response()->json([
                    'bEstatus' => false,
                    'cMensagge' => 'Reservacion hecha',
                    'data' => $dataSend                    
                ]);
            }
            else{
                return response()->json([
                    'bEstatus' => true,
                    'cMensagge' => 'Ocurrion un error - Contacte a su administrador'
                ]);
            }

        }
        catch(\Illuminate\Database\QueryException $ex){
            
            return response()->json([
                'lError' => true,
                'cError' => $ex->getMessage()
            ]);
        }
        

        return response()->json([
            "recibido" => $dataPaypal, 
            "idRes" => strval($reservation_code),
            "idClient" => strval($client->id)
        ]);         

    }

    public function EnviarCorreo($datos){
                    
        $items = $datos;
        
        $arrayText = [
            "hotel_s"=> "Hotel",
            "total_s"=> "Mount Total",
            "passenger_s"=> "Passenger",
            'firstName' => "First Name",
            "lastName"=> "Last Name",
            "email"=> "Email",
            "phone"=> "Phone",
            "airbnb"=> "Destination",
            "airlineArrival"=> "Airline Arrival",
            "flightNumberArrival"=> "Flight Number Arrival",
            "arrivalHourArrival"=> "Arrival Hour",
            "arrival_date_s" => "Arrival Date",
            "airlineDeparture"=> "Airline Departure",
            "flightNumberDeparture"=> "Flight Number Departure",
            "arrivalHourDeparture"=> "Departure Hour",  
            'departure_date_s'  => "Departure Date",            
            "reservation_code" => "Reservation Code",
            "payment_type" => "Payment Method"            
        ];

        foreach($items as $index => $value){
            if(isset($arrayText[$index])){
                $newArray[$arrayText[$index]] = $value;
            }
            else{
                $newArray[$index] = $value;
            }            
        }
                
        $items = $newArray;

        $TemplateMail = view('template_mail.reservation_mail')->with('datos',$items);//->with('Neto', number_format($SumaNeta,2))->render(); 

        Mail::html($TemplateMail, function($message) use ($items) {            
            $mailDire[] = $items['Email'];//Auth::user()->email;//$User->email;
            $mailDire[] = 'reservations@altripscancun.com';
            $message->subject($this->globalTest.'Altrips - My Reservation - '.$items['Reservation Code'])->to($mailDire);            
        });
    }

    public function testSelect(){
        
        // $datos['email'] = 'japj784@gmail.com';                 
        // $datos['reservation_code'] = "ATC-105";
        // $datos['firstName'] = "Jesús";
        // $datos['lastName'] = "Pazos";
        // $datos['payment_type'] = "Paypal";
        // $datos['phone'] = "9992189812";
        // $datos['airlineArrival'] = "America Airlines";
        // $datos['type_trip_s'] = "3";                     
        // $datos["hotel_s"] = "Playa del Carmen Hotel by H&A";
        // $datos["total_s"] = "85.00";
        // $datos["passenger_s"] = "3";
        // $datos["airbnb"] = "Cancun airport";
        // $datos["flightNumberArrival"] = "0000";
        // $datos["arrivalHourArrival"] = "12:00 AM";
        // $datos["arrival_date_s"]  = "30-November-2023";
        // $datos["airlineDeparture"] = "Colombian";
        // $datos["flightNumberDeparture"] = "1009";
        // $datos["arrivalHourDeparture"] = "12:00 AM";
        // $datos['departure_date_s']   = "25-November-2023";
        // $datos["payment_type"]  = "PAYPAL";
        

        // $this->EnviarCorreo($datos);        

        //$fecha = "11-September-2023";
        $fecha = "12/12/2023";
        // $fecha = "2023/11/20";
        // $fecha = "12/12/2023";

        $fecha = str_replace("/","-",$fecha);

        //mmddyyyy
        //ddmmyyyy
        
        $timestamp = (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) 
                    ? 'Y-m-d' : ((preg_match('/\b(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])-\d{4}\b/', $fecha)) 
                                ? 'm-d-Y' : ((preg_match('/\b(0[1-9]|[1-2][0-9]|3[0-1])-(0[1-9]|1[0-2])-\d{4}\b/', $fecha)) ? 'd-m-Y' : "No encontre Formato" ));

        return response()->json([
            "Format" => $timestamp,
            "Fecha" => $fecha
        ]);

        if(!preg_match("/[a-z]/i", $fecha)){
            $fecha = strtotime($fecha);
            $fecha = date('d-F-Y',$fecha);
            return response()->json([
                "FechaFormat" => $fecha,
                'urlFull' => url(),
                'local' => $_SERVER['HTTP_HOST']
            ]);
        }

        // $hora = "5:47 AM";
        // $hora = trim(str_replace(['AM','PM'], '', $hora));
        // return response()->json([
        //     'HoraFix' => $hora
        // ]);

        // $reservation_code = Sale::orderBy('id', 'DESC')->first(['id']);
        // return response()->json(["id" => $reservation_code->id]);        
    }

    public function testJSON(){

        $datos = [
                "intent" => "CAPTURE",
                "purchase_units" => [
                    [
                        "amount" =>[
                            "currency_code" => "USD",
                            "value" => "230.00"
                        ]
                    ]
                ]
                ,"payment_source" => [
                    "paypal" => [
                        "experience_context" => [
                            "payment_method_preference" => "IMMEDIATE_PAYMENT_REQUIRED",
                            "brand_name" => "EXAMPLE INC",
                            "locale" => "en-US",
                            "landing_page" => "LOGIN",
                            "shipping_preference" => "SET_PROVIDED_ADDRESS",
                            "user_action" => "PAY_NOW",
                            "return_url" => "https://example.com/returnUrl",
                            "cancel_url" => "https://example.com/cancelUrl",
                            "HOL" => "que pex"
                        ]
                    ]
                ]
            ];
        
        echo json_encode($datos);

    }

    public function generaOrden(Request $request){

        $token = $this->generateAccessToken();
        
        try{

            $url   = $this->UrlPay["UrlPaypal"]."/v2/checkout/orders"; 
            $header = [
                "User-Agent" => "allTripsCancun",
                'Authorization' => "Bearer ".$token,
                "Accept" => "*/*",
                "content-type" => "application/json",                
            ];    
            
            $ret = [];
            foreach($header as $item => $value) {
                $ret[] = "$item: $value";
            } 

            $datos = [
                "intent" => "CAPTURE",
                "purchase_units" => [
                    [
                        "amount" =>[
                            "currency_code" => "USD",
                            "value" => $request->mount
                        ]
                    ]
                ]
                ,
                // "application_context" => [
                //     "shipping_preference" => "NO_SHIPPING"
                // ],
                // "payer" => [
                //     "email_address" => 'japj784@gmail.com',
                //     "name" => [
                //         "given_name" => "SIgma",
                //         "surname" => "Cauich"
                //     ],
                //     "address" => [
                //         "country_code" => "MXN"
                //     ]
                // ]

                // ,"payment_source" => [
                //     "paypal" => [
                //         "experience_context" => [
                //             "payment_method_preference" => "IMMEDIATE_PAYMENT_REQUIRED",
                //             "brand_name" => "EXAMPLE INC",
                //             "locale" => "en-US",
                //             "landing_page" => "LOGIN",
                //             "shipping_preference" => "SET_PROVIDED_ADDRESS",
                //             "user_action" => "PAY_NOW",
                //             "return_url" => "https://example.com/returnUrl",
                //             "cancel_url" => "https://example.com/cancelUrl"
                //         ]
                //     ]
                // ]
            ];

            $respuesta = $this->GetCurl($url,true,$datos,$ret);        
            //return json_decode($respuesta,true)["id"];
            return response()->json(json_decode($respuesta,true));        
        }
        catch(\Exception $ex){
            
            return response()->json([
                'lError' => true,
                'cError' => $ex->getMessage()
            ]);
        }
    }

    function GetCurl($url, $method, $data, $header = false){

        
        $data = (is_array($data)) ? json_encode($data) : $data;
        
        $curl = curl_init();            

        if($method){

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER=>false,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30000,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $data,                
                CURLOPT_HTTPHEADER => $header
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                return "cURL Error #:" . $err;
            } else {              
                return $response;
            }   
        }
        else{

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://example.com",// your preferred link
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_TIMEOUT => 30000,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    // Set Here Your Requesred Headers
                    'Content-Type: application/json',
                ),
            ));
            
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                print_r(json_decode($response));
            }
        }                    
    }

    public function validationFormTrip($data_trip){

        // $arrayNoValidate = ["airbnb","submit","hotel_s","total_s","passenger_s"];
        $arrayNoValidate = [];
        $arrayText = [
            "hotel_s"=> "Hotel",
            "total_s"=> "Mount Total",
            "passenger_s"=> "Passenger",
            'firstName' => "First Name",
            "lastName"=> "Last Name",
            "email"=> "Email",
            "phone"=> "Phone",
            "airbnb"=> "Airbnb",
            "airlineArrival"=> "Airline Arrival",
            "flightNumberArrival"=> "Flight Number Arrival",
            "arrivalHourArrival"=> "Arrival Hour",
            "airlineDeparture"=> "Airline Departure",
            "flightNumberDeparture"=> "Flight Number Departure",
            "arrivalHourDeparture"=> "Departure Hour",
            "departure_date_s" => "Departure Date",
            "arrival_date_s" => "Arrival Date",
            "zone_s" => "Zone Hotel",            
        ];

        // print_r($data_trip);
        $respuesta['bEstatus'] = true;
        $respuesta['cMensaje'] = '';
        
        foreach($arrayText as $KeyValue => $valor){
            // if(!in_array($KeyValue, $data_trip)){
            if (!array_key_exists($KeyValue, $data_trip)) {
                $respuesta['bEstatus'] = false;
                $respuesta['cMensaje'] = "The field ".$arrayText[$KeyValue]." is required.";
                break;
            }
            else{
                if(empty($valor)){
                    $respuesta['bEstatus'] = false;
                    $respuesta['cMensaje'] = "The field ".$arrayText[$KeyValue]." is not empty.";
                    break;
                }
            }          
        }

        /*foreach ($data_trip as $key => $value) {            
            if(!in_array($key, $arrayNoValidate)){
                if(empty($value)){
                    $respuesta['bEstatus'] = false;
                    $respuesta['cMensaje'] = "The field ".$arrayText[$key]." is required.";
                    break;
                }
            }
        }*/

        return $respuesta;
    }

    public function convertHoraGeneralizada($fecha){

        if(preg_match("/[a-z]/i", $fecha)){
            return $fecha;
        }

        $fecha = str_replace('/','-',$fecha);
        $fecha = strtotime($fecha);
        $fecha = date('d-F-Y',$fecha);
        return $fecha;
    }
}
