<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use App\Models\Entities\User as User;

class TokenVerification
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    
    public function handle($request, Closure $next, $guard = null){
        // if ($this->auth->guard($guard)->guest()) {
        //     return response()->json(['Unauthorized.' => 401]);
        // }

        //JPJ
        // if($request->api_token != ""){
        //  $DateActual = date('Y-m-d H:i:s');
        //  $User = User::where('api_token',$request->api_token)->first()->toArray();


        //  //$time = ($DateActual - $User['create_token']);

        //  // if(($DateActual))

        //  return response()->json(['Autorizado.' => $User]);
        // }
        //JPJ

        $token = null;
        $headers = apache_request_headers();    
        // dd($headers);    
        // dd($request->header('X-Requested-With'));

        // return response()->json([
        //     'cabecera' => $headers
        // ]);

        if(!isset($headers['X-Requested-With'])){            
            return response()->json([
                'Unauthorized' => 401,
                'lEstatus' => 'Header not found'
            ]);
        }
        else{    

            $token = $request->header('X-Requested-With');            
            
            $User = User::where('api_token',$token)->first();

            if(empty($User)){
                return response()->json([
                    'Unauthorized' => 401, 
                    'lEstatus' => 'Token not found'
                ]);
            }
            
            $HourNow = date('Y-m-d H:i:s');            
            $dt1 = new \DateTime($HourNow);
            $dt2 = new \DateTime($User->create_token);        
            $interval = $dt1->diff($dt2);            
            $totalMinutos=($interval->d * 24 * 60) + ($interval->h * 60) + $interval->i;

            if($totalMinutos > 30){
                
                User::where('api_token',$token)->update(['api_token' => null, 'create_token' => null]);

                return response()->json([
                    'lEstatus' => false,
                    'cEstatus' => 'Please login again',
                    'redirect' => url()."/api/login"
                ]);
            }

            // dd("Total minutos: $totalMinutos");
            // dd('%d days, %d hours, %d minutes', $hours->d, $hours->h, $hours->i);

            // dd($User->create_token);                    
        } 
        
        return $next($request);
    }
}
