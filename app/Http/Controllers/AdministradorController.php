<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Entities\Zone as Zona;
use App\Models\Entities\User as User;


class AdministradorController extends Controller
{
	public function CreateUser(Request $request){

		$input = $request->all();
		$input['password'] = Hash::make($request->password); 

		User::create($input);

		return response()->json([
			'lError' => false,
			'cMessage' => "Usuario registrado"
		]);
	}

	public function login(Request $request){

		$user = User::whereEmail($request->email)->first();

		if(!is_null($user) && Hash::check($request->password, $user->password)){
			
			$user->api_token = Str::random(150);
			$user->save();

			return response()->json([
				'lError' => false,
				'cMessage' => "Welcome " .$user->name,
				'token' => $user->api_token
			]);
		}
	}
}