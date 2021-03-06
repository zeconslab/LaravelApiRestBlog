<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Lcobucci\JWT\Exception;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

    /**
     * Crea una nueva instancia de AuthController.
     *
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' =>['login', 'register']]);
    }

    /**
     * Valida la peticcion http que coincida con el usuario registrad en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return response
     */
    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);

            if(!$token=auth()->attempt($credentials))
            {
                return response()->json([
                    'error' => 'Unauthorized',
                    'status' => 401,
                    'message' => 'Contraseña o Usuario incorrecto'
                ],401);
            }
            else {
                return response()->json([
                    'success' => 'true',
                    'status' => 200,
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth()->factory()->getTTL() * 60,
                    'user' => Auth::user(),
                ], 200);
            }

            // return $this->respondWithToken($token);
        
    }


    /**
     * Metodo para registrar usuarios en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return response
     */
    public function register(Request $request)
    {
        $rules =[
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8'
        ];

        $messages = [
            'name.required' => 'El :attribute es un campo requerido',
            'email.required' => 'El :attribute es un campo requerido',
            'password.required' => 'La :attribute es un campo requerido'
        ];

        $attributes = [
            'email' => 'Correo electronico',
            'name' => 'Nombre',
            'password' => 'Contraseña'
        ];

            $validator = Validator::make($request->all(), $rules , $messages, $attributes);
    
            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 422);
            }

            $passecrypted = Hash::make($request->password);

            $userNew = new User();
            $userNew->name = $request->name;
            $userNew->email = $request->email;
            $userNew->password = $passecrypted;
            $userNew->save();
            return $this->login($request);
        
    }

    /**
     * Metodo para cerrar sesion.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return response
     */
    public function logout(Request $request)
    {
        JWTAuth::invalidate(JWTAuth::parseToken($request->token));
        return response()->json([
            'status' => 200,
            'message' => 'Session cerrada con exito'
        ]);
    }

}
