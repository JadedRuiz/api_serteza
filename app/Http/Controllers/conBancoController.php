<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CatBanco;

class conBancoController extends Controller
{

   public function BancosAutocomplete(Request $res)
   {
    
       $bancos = DB::table('bat_catbancos as cb')
       ->select("cb.id_catbanco", DB::RAW("concat(satB.concepto,'-',RIGHT(cb.cuenta,4) as banco"))
       ->join("sat_catbancos as satB","satB.id_bancosat","=","cb.id_bancosat")
       ->where(function ($query) use ($res){
            if($res["palabra"] != ""){
                $palabra = "%".$res["palabra"]."%";
                $query->where("cb.id_empresa",$res["id_empresa"])
                ->Where("satB.descripcion","like",$palabra)
                >orWhere("cb.cuenta","like",$palabra);
            }
        })
        ->orderBy("satB.descripcion","ASC")
        ->orderBy("cb.cuenta","ASC")
        ->limit(100)
        ->get();
        if(count($bancos)>0){
            return $this->crearRespuesta(1,$bancos,200);
        }
        return $this->crearRespuesta(2,"No se tiene registros en la BD",200);
   }
   
   public function BancosEmpresa($id_empresa)
   {
       $bancos = DB::table('ban_catbancos as cb')
       ->select("cb.id_catbanco", DB::RAW("concat(satB.descripcion,'-',RIGHT(cb.cuenta,4)) as banco"),
                 "cb.id_bancosat", "cb.cuenta", "cb.tarjeta", "cb.clabe", "cb.contrato", "cb.cuentacontable")
       ->join("sat_catbancos as satB","satB.id_bancosat","=","cb.id_bancosat")
       ->where("cb.id_empresa",$id_empresa)
       ->orderBy("satB.descripcion","ASC")
       ->orderBy("cb.cuenta","ASC")
       ->get();
       if(count($bancos)>0){
           return $this->crearRespuesta(1,$bancos,200);
       }
       return $this->crearRespuesta(2,"No se han encontrado registros con esta empresa",200);
   }
   public function ObtenerBancoPorId($id_catbanco)
   {

    $bancos = DB::table('bat_catbancos as cb')
       ->select("cb.id_catbanco", DB::RAW("concat(satB.concepto,'-',RIGHT(cb.cuenta,4) as banco"),
                 "cb.id_bancosat", "cb.cuenta", "cb.tarjeta", "cb.clabe", "cb.contrato", "cb.cuentacontable")
       ->join("sat_catbancos as satB","satB.id_bancosat","=","cb.id_bancosat")
       ->where("cb.id_catbanco",$id_catbanco)
       ->orderBy("satB.descripcion","ASC")
       ->orderBy("cb.cuenta","ASC")
       ->first();

    if($bancos){
        return $this->crearRespuesta(1,$bancos,200);
    }
    return $this->crearRespuesta(2,"No se ha encontrado el concepto",200);
   }

   public function AltaBanco(Request $res)
   {
        $banco = new CatBanco();
        if($res["id_empresa"] == 0){
            return $this->crearRespuesta(2,"Debe capturar el ID Empresa, es un campo obligatorio",200);
        }
        if($res["id_bancosat"] == 0){
            return $this->crearRespuesta(2,"Debe capturar el ID del Banco Sat, es obligatorio",200);
        }
        if($res["cuenta"] == ""){
            return $this->crearRespuesta(2,"El número de cuenta, es obligatorio",200);
        }

        

        try{

            $buscar = CatBanco::select("id_catbanco")
            ->where("cuenta",$res["cuenta"])
            ->get();
            if(count($buscar)>0){
                return $this->crearRespuesta(2,"EL BANCO HA SIDO CAPTURADO ANTERIORMENTE",200);
            }else{

                $fecha = $this->getHoraFechaActual();
                $banco->id_empresa = $res["id_empresa"];
                $banco->id_bancosat = $res["id_bancosat"];
                $banco->cuenta = $res["cuenta"];
                $banco->tarjeta = $res["tarjeta"];
                $banco->clabe = $res["clabe"];
                $banco->contrato = $res["contrato"];
                $banco->cuentacontable = $res["cuentacontable"];
                $banco->fecha_creacion = $fecha;
                $banco->usuario_creacion = $res["usuario"];
                $banco->save();
                return $this->crearRespuesta(1,"Banco guardado",200);
            }
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),200);
        }   
   }
   public function ModificarBanco(Request $res)
   {
        if($res["id_empresa"] == 0){
            return $this->crearRespuesta(2,"Debe capturar el ID Empresa, es un campo obligatorio",200);
        }
        if($res["id_bancosat"] == 0){
            return $this->crearRespuesta(2,"Debe capturar el ID del Banco Sat, es obligatorio",200);
        }
        if($res["cuenta"] == ""){
            return $this->crearRespuesta(2,"El número de cuenta, es obligatorio",200);
        }
            $fecha = $this->getHoraFechaActual();
            $banco = CatBanco::find($res["id_catbanco"]);
            $banco->id_bancosat = $res["id_bancosat"];
            $banco->cuenta = $res["cuenta"];
            $banco->tarjeta = $res["tarjeta"];
            $banco->clabe = $res["clabe"];
            $banco->contrato = $res["contrato"];
            $banco->cuentacontable = $res["cuentacontable"];
            $banco->fecha_modificacion = $fecha;
            $banco->usuario_modificacion = $res["usuario"];
            $banco->save();
            return $this->crearRespuesta(1,"Banco modificado",200);
        
   }
   
}
