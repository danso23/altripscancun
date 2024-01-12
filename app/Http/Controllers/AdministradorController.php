<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Validator; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Entities\Zone as Zona;
use App\Models\Entities\User as User;


class AdministradorController extends Controller
{
	public function CreateUser(Request $request){

		$input = $request->all();
		

		$validatedData = $this->validate($request,[
        	'email' => 'unique:users|max:255',
    	],[
    		'email.unique' => 'email with account, please try with other'
    	]);
		
		$input['password'] = Hash::make($request->password); 
		$User = User::create($input);

		return response()->json([
			'lEstatus' => false,
			'cEstatus' => "Usuario registrado"
		]);
	}

	public function login(Request $request){

		$user = User::whereEmail($request->email)->first();

		if(!is_null($user) && Hash::check($request->password, $user->password)){
			
			$user->api_token = Str::random(150);
			$user->create_token = date('Y-m-d H:i:s');
			$user->save();

			return response()->json([
				'lEstatus' => false,
				'cEstatus' => "Welcome " .$user->name,
				'token' => $user->api_token
			]);
		}
		else{
			return response()->json([
				'lEstatus' => true,
				'cEstatus' => "Sorry, User name o Password Incorrect",				
			]);
		}
	}

	public function logout(Request $request){

		$headers = apache_request_headers();
		$headers['X-Requested-With'];

		// $User = User::where('api_token',$request->api_token)->first();//->toArray();
		$User = User::where('api_token',$headers['X-Requested-With'])->first();//->toArray();
		
		if(!is_null($User)){

			$User->api_token = null;
			$User->create_token = null;
			$User->save();
			
			return response()->json([
				'lEstatus' => false,
				'cEstatus' => "Good bye " .$User->name				
			]);
		}
		else{
			return response()->json([
				'lEstatus' => true,
				'cEstatus' => "Sorry, occurre a erro, please try again",				
			]);
		}
	}
}