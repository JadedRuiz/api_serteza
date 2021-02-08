<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UsuarioController extends Controller
{
    public function __construct()
    {
    }
    public function obtenerUsuarios(){
        return User::all();
    }
    public function register(Request $request)
    {
        //validate incoming request 
        $this->validate($request, [
            'nombre' => 'required|string|max:150',
            'usuario' => 'required|unique:seg_cat_usuario|max:50',
            'password' => 'required|max:50',
        ]);

        // try {
           
            $user = new User;
            $user->nombre = $request->input('nombre');
            $user->usuario = $request->input('usuario');
            $plainPassword = $request->input('password');
            $user->password = app('hash')->make($plainPassword);
            $user->fecha_creacion = $this->getHoraFechaActual();
            $user->cat_usuario_c_id = 1;
            $user->activo = 1;

            $user->save();

            //return successful response
            return response()->json(['user' => "", 'message' => 'CREATED'], 201);

        // } catch (\Exception $e) {
        //     //return error message
        //     return response()->json(['message' => 'User Registration Failed!'.$e], 409);
        // }

    }
    public function login(Request $res)
    {
        $this->validate($res,[
            "usuario" => "string|required|max:50",
            "password" => "string|required|max:50"
        ]);
        $credentials = array(
            'usuario' => $res["usuario"], 
            'password' => $res["password"]
        );
        
        if (! $token = Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        //response()->json([$this->respondWithToken($token), 'users' =>  User::all()], 201);
        
        return response()->json(['credenciales'=>$this->respondWithToken($token), 
        'user' =>  User::select('nombre')->where('usuario', $res["usuario"])->first()], 201);
    }

}
