<?php

namespace App\Http\Middleware;

use Closure;

class ExampleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $headers = [
            'Access-Control-Allow-Origin'      => '*',
            'Access-Control-Allow-Methods'     => 'POST, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age'           => '86400',
            'Access-Control-Allow-Headers'     => 'Content-Type, Authorization, X-Requested-With'
        ];

        //return response()->json(["response" => $headers]);

        if ($request->isMethod('OPTIONS')){
            return response()->json('{"method":"OPTIONS"}', 200, $headers);
        }

        $response = $next($request);
        foreach($headers as $key => $value)
        {
            $response->header($key, $value);
        }

        return $response;
    //     return $next($request)
    //         ->header('Access-Control-Allow-Origin', '*')
    //         ->header('Access-Control-Allow-Methods', 'POST, OPTIONS, PUT, DELETE')
    //         ->header('Access-Control-Allow-Headers',' Origin, Content-Type, Accept, Authorization, X-Request-With')
    //         ->header('Access-Control-Allow-Credentials',' true');
    }
}
