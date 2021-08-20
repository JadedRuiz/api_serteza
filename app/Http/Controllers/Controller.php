<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

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
    public function getEstatus($tipo){
            $data = '';
        if($tipo = "cancelar"){
                $data = DB::table('gen_cat_statu')
                ->select('id_statu')
                ->where('status', "Cancelado")
                ->first();
                $data = $data->id_statu;
        }
        if($tipo = "activo"){
                $data = DB::table('gen_cat_statu')
                ->select('id_statu')
                ->where('status', "Activo")
                ->first();
                $data = $data->id_statu;
        }
        return $data;
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
    public function getConceptoDefault(){
        $data = DB::table('con_catconceptos')
        ->select('id_concepto')
        ->where('concepto', 'Por clasificar')
        ->first();
        return $data->id_concepto;
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
                return (intval($id)+1);
        }elseif(count($utlimo) == 0){
                return 1;
        }
    }
    public function decode_json($code){
        $ultimoCharacter = substr($code,-1);
        $restante = substr($code,0,-1);
        $digitos = substr($restante,-2);
        $longitud = $this->convert1toInvers($digitos[0]).$this->convert1toInvers($digitos[1]);
        $iteraciones = substr($code,0,1);
        $ite=  $this->convertAto1($iteraciones);
        $descrypt = substr($code,0,$longitud+1);
        $descrypt = substr($descrypt,1);
        $cola = substr($code,0,-3);
        $cola = substr($cola,$longitud+1);
        for($i=0; $i<$ite; $i++){
                $descrypt= base64_decode($descrypt);
        }
        $resu = $descrypt.$cola.$ultimoCharacter;
        return base64_decode($resu);
    }
    public function encode_json($code){
        $rand = rand(3,9);
        $base_64 = base64_encode($code);
        $cabecera = substr($base_64, 0, 3);
        $cola = substr($base_64,3);
        for($r =0; $r<$rand; $r++){
                $cabecera = base64_encode($cabecera);
        }
        $longitud = strlen($cabecera);
        $cabecera_cola = substr($cola, 0, -1);
        $cola_cola = substr($cola, -1);
        $longitud_1er = substr($longitud,0,1);
        $longitud_2do = substr($longitud, -1);
        $letras = $this->convert1toInvers($longitud_1er) . $this->convert1toInvers($longitud_2do);
        $letra_rand = $this->convertAto1($rand);
        return $letra_rand.$cabecera.$cabecera_cola.$letras.$cola_cola;
    }
    public function cambiarDeEstatus($id_candidato,$id_status)
    {
        try{
            DB::update('update rh_cat_candidato set id_status = ? where id_candidato = ?', [$id_status,$id_candidato]);
            return ["ok" => true];
        }catch(Throwable $e){
            return ["ok"=> false, "message"=>$e->getMessage()];
        }
    }
    public function convertAto1($num){
        if(is_numeric($num)){
            switch($num) {
                case 3: return "e";
                        break;
                case 4: return "A";
                        break;
                case 5: return "r";
                        break;
                case 6: return "M";
                        break;
                case 7: return "z";
                        break;
                case 8: return "L";
                        break;
                case 9: return "S";
                        break; 
            }
        }else{
            switch($num) {
                case "e": return 3;
                        break;
                case "A": return 4;
                        break;
                case "r": return 5;
                        break;
                case "M": return 6;
                        break;
                case "z": return 7;
                        break;
                case "L": return 8;
                        break;
                case "S": return 9;
                        break;
                        
            }
        }
    
    }
    public function convert1toInvers($num){
        if(is_numeric($num)){
            switch($num) {
                case 0: return "z";
                        break;
                case 1: return "Y";
                        break;
                case 2: return "x";
                        break;
                case 3: return "W";
                        break;
                case 4: return "v";
                        break;
                case 5: return "U";
                        break;
                case 6: return "t";
                        break;
                case 7: return "S";
                        break;
                case 8: return "r";
                        break;
                case 9: return "Q";
                        break;
                        
            }
        }else{
            switch($num) {
                case "z": return 0;
                        break;
                case "Y": return 1;
                        break;
                case "x": return 2;
                        break;
                case "W": return 3;
                        break;
                case "v": return 4;
                        break;
                case "U": return 5;
                        break;
                case "t": return 6;
                        break;
                case "S": return 7;
                        break;
                case "r": return 8;
                        break;
                case "Q": return 9;
                        break;
            }
        }
    
    }
    public function obtenerCatalogo($nombre_tabla, $columnas)
    {
        $recuperar_catalogo = DB::table($nombre_tabla)
        ->get();
        if($nombre_tabla == "gen_cat_estados"){
                $recuperar_catalogo = DB::table($nombre_tabla)
                ->orderBy("estado","ASC")
                ->get();
        }
        
        if(count($recuperar_catalogo)>0){
                return $recuperar_catalogo;
        }
    }
    public function obtenerCatalogoAutoComplete(Request $res){
        $nombre_columa_busqueda = $res["nombre_columna"];
        $nombre_tabla = $res["nombre_tabla"];
        $busqueda = "%".strtoupper($res["busqueda"])."%";
        $filtros = $res["filtros"];
        $select = $res["select"];
        $buscar = DB::table($nombre_tabla)
        ->select($select)
        ->where(function ($query) use ($filtros,$nombre_columa_busqueda,$busqueda){
                if(count($filtros)>0){
                        foreach($filtros as $filtro){
                                if($filtro["tipo"] == "where"){
                                        $query->where($filtro["columna"],$filtro["dato"]);
                                }
                                if($filtro["tipo"] =="whereIn"){
                                        $query->whereIn($filtro["columna"],$filtro["datos"]);
                                }
                        }
                }
                $query->where($nombre_columa_busqueda,"Like",$busqueda);
        })
        ->get();

        if(count($buscar)>0){
                return $this->crearRespuesta(1,$buscar,200);
        }
    }
    public function obtenerMovimientos($id_empresa)
    {
        $recuperar_id_clientes = DB::table('liga_empresa_cliente')
        ->select("id_cliente")
        ->where("id_empresa",$id_empresa)
        ->get();
        if(count($recuperar_id_clientes)>0){
           $id_clientes = [];
           foreach($recuperar_id_clientes as $id_cliente){
             array_push($id_clientes,$id_cliente->id_cliente);
           }
           $movimientos = DB::table('rh_movimientos as rm')
           ->select("rm.tipo_movimiento","rm.id_status","gcc.cliente","rm.id_movimiento","rm.fecha_movimiento")
           ->join("gen_cat_cliente as gcc","gcc.id_cliente","=","rm.id_cliente")
           ->whereIn("rm.id_cliente",$id_clientes)
           ->where("rm.id_status",5)
           ->where("rm.activo",1)
           ->get();
           foreach($movimientos as $movimiento){
             $movimiento->fecha_movimiento =  date("Y-m-d",strtotime($movimiento->fecha_movimiento));
             switch($movimiento->tipo_movimiento){
                case "A":
                  $movimiento->tipo_movimiento = "Alta";
                  break;
                case "M":
                  $movimiento->tipo_movimiento = "Modificación";
                  break;
                case "B":
                  $movimiento->tipo_movimiento = "Baja";
                  break;
             }
           }
           return $movimientos;
        }
    }
}
