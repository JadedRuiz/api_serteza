<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    protected function respondWithToken($token)
    {
        return [
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ];
    }

    public function getHoraFechaActual(){
        $mytime = Carbon::now();
        return $mytime;
    }

    public function crearRespuesta($tipo,$obj,$http_response){
        if($tipo == 1){ //Success
            return response()->json(['ok' => true, 'data' => $obj], $http_response);
        }
        if($tipo == 2) {    //Failed
            return response()->json(['ok' => false, 'data' => $obj], $http_response);
        }
    }
    public function getEnv($nombre){
        return env($nombre,"");
    }
    public function getSigId($nombre_tabla){
        $bol = true;
        $utlimo = DB::table($nombre_tabla)
        ->get();
        if(count($utlimo) > 0){
                $utlimo = $utlimo->last();
                $id = "";
                foreach($utlimo as $parametro){
                        if($bol){
                                $id = $parametro;
                                $bol = false;
                        }
                }
                return $id+1;
        }elseif(count($utlimo) == 0){
                return 1;
        }
    }
}
