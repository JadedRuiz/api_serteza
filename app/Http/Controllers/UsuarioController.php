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

        try {
           
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
            return $this->crearRespuesta(1,"Usuario registrado con éxito",201);

        } catch (\Throwable $th) {
            return $this->crearRespuesta(2,"Ha ocurrido un error: ".$th->getMessage(),301);
        }

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
            return response()->json(['message' => 'Las credenciales no son validas, intente de nuevo'], 401);
        }
        $usuario = DB::table("seg_cat_usuario")
        ->select("id","nombre","usuario")
        ->where("usuario",$res["usuario"])
        ->get();
        $sistemas = DB::table("liga_usuario_sistema as lus")
        ->join("gen_cat_sistemas as gce","gce.id","=","lus.cat_sistemas_id")
        ->select("gce.id","gce.sistema")
        ->where("lus.cat_usuario_id",$usuario[0]->id)
        ->where("lus.activo",1)
        ->get();
        $sistemas_info = [];
        foreach($sistemas as $sistema){
            array_push($sistemas_info,[
                "id" => $sistema->id,
                "sistema" => $sistema->sistema
            ]);
        }
        $respuesta = [
            "token_acesso" => $this->respondWithToken($token),
            "info_usuario" => [
                "id" => $usuario[0]->id,
                "nombre" => $usuario[0]->nombre,
                "url_foto" => $this->getEnv("APP_URL")."/api_serteza/resources/img/foto_perfil_".$usuario[0]->id.".png",
                "usuario" => $res["usuario"],
                "sistemas" => $sistemas_info
            ]
        ];
        return $this->crearRespuesta(1,$respuesta,200);
    }

}
