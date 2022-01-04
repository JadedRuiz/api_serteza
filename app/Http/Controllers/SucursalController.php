<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SucursalController extends Controller
{
    public function autocomplete(Request $res)
    {
        $id_empresa = $res["id_empresa"];
        $palabra = "%".strtoupper($res["palabra"])."%";
        $sucursales = DB::table('nom_sucursales')
        ->select("id_sucursal","sucursal")
        ->where("id_empresa",$id_empresa)
        ->where("activo",1)
        ->where(function ($query) use ($palabra){
            $query->orWhere("sucursal", "like", $palabra)
            ->orWhere("id_sucursal", "like", $palabra);
        })
        ->get();
        if(count($sucursales)>0){
            return $this->crearRespuesta(1,$sucursales,200);
        }else{
            return $this->crearRespuesta(2,"No se han encontrado resultados",200);
        }
    }
    public function obtenerSucursales($id_empresa)
    {
        $sucursales = DB::table('nom_sucursales as ns')
        ->select("sucursal","gcc.cliente","id_sucursal","zona","representante_legal as repre")
        ->join("gen_cat_cliente as gcc","gcc.id_cliente","=","ns.id_cliente")
        ->where("id_empresa",$id_empresa)
        ->where("ns.activo",1)
        ->get();
        if(count($sucursales)>0){
            return $this->crearRespuesta(1,$sucursales,200);
        }else{
            return $this->crearRespuesta(2,"No se han encontrado sucursales",200);
        }
    }
    public function obtenerSucursalPorIdSucursal($id_sucursal)
    {
        $sucursal = DB::table('nom_sucursales as ns')
        ->select("sucursal","id_cliente","id_sucursal","zona","region","tasaimpuestoestatal","tasaimpuestoespecial","prima_riesgotrabajo","gcd.id_direccion","gcd.calle", "gcd.numero_interior", "gcd.numero_exterior", "gcd.cruzamiento_uno", "gcd.cruzamiento_dos", "gcd.codigo_postal", "gcd.colonia", "gcd.localidad", "gcd.municipio", "gcd.estado", "gcd.descripcion as descripcion_direccion")
        ->leftJoin("gen_cat_direccion as gcd","gcd.id_direccion","=","ns.id_direccion")
        ->where("id_sucursal",$id_sucursal)
        ->where("ns.activo",1)
        ->get();
        if(count($sucursal)>0){
            return $this->crearRespuesta(1,$sucursal,200);
        }else{
            return $this->crearRespuesta(2,"No se han encontrado la sucursal",200);
        }
    }
    public function crearSucursal(Request $res)
    {
        try{
            $id_empresa = $res["id_empresa"];
            $id_cliente = $res["id_cliente"];
            $id_sucursal = $this->getSigId("nom_sucursales");
            $sucursal = strtoupper($res["sucursal"]);
            $zona = $res["zona"];
            $tasa_estatal = $res["tasa_estatal"];
            $tasa_especial = $res["tasa_especial"];
            $prima_riesgo = $res["prima_riesgo"];
            $estado = $res["estado"];
            $region = $res["region"];
            $usuario_creacion = $res["usuario"];
            $fecha = $this->getHoraFechaActual();
            DB::insert('insert into nom_sucursales (id_sucursal, id_empresa, id_cliente, sucursal, zona, region, tasaimpuestoestatal, tasaimpuestoespecial, prima_riesgotrabajo, id_estado, usuario_creacion, fecha_creacion, activo) values (?,?,?,?,?,?,?,?,?,?,?,?,?)', [$id_sucursal,$id_empresa,$id_cliente,$sucursal,$zona,$region,$tasa_estatal,$tasa_especial,$prima_riesgo,$estado,$usuario_creacion,$fecha, 1]);
            return $this->crearRespuesta(1,"Sucursal creada",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }

    public function modificarSucursal(Request $res)
    {
        try{
            $sucursal = DB::table('nom_sucursales')
            ->where("id_sucursal",$res["id_sucursal"])
            ->first();
            $sucursal_name = strtoupper($sucursal->sucursal);
            $zona = $sucursal->zona;
            $tasa_estatal = $sucursal->tasaimpuestoestatal;
            $tasa_especial = $sucursal->tasaimpuestoespecial;
            $prima_riesgo = $sucursal->prima_riesgotrabajo;
            $estado = $sucursal->id_estado;
            $region = $sucursal->region;
            if($res["sucursal"] != "" && $sucursal_name != $res["sucursal"]){
                $sucursal_name = strtoupper($res["sucursal"]);
            }
            if($res["zona"] != "" && $zona != $res["zona"]){
                $zona = $res["zona"];
            }
            if($res["tasa_estatal"] != "" && $tasa_especial != $res["tasa_estatal"]){
                $tasa_estatal = $res["tasa_estatal"];
            }
            if($res["tasa_especial"] != "" && $tasa_especial != $res["tasa_especial"]){
                $tasa_especial = $res["tasa_especial"];
            } 
            if($res["prima_riesgo"] != "" && $prima_riesgo != $res["prima_riesgo"]){
                $prima_riesgo = $res["prima_riesgo"];
            }
            if($res["estado"] != "" && $estado != $res["estado"]){
                $estado = $res["estado"];
            }
            if($res["region"] != "" && $region != $res["region"]){
                $region = $res["region"];
            }
            $usuario_creacion = $res["usuario"];
            $fecha = $this->getHoraFechaActual();
            DB::update('update nom_sucursales set sucursal = ?, tasaimpuestoestatal = ?, tasaimpuestoespecial = ?, prima_riesgotrabajo = ?, id_estado = ?, zona = ?, region = ? where id_sucursal = ?', [$sucursal_name,$tasa_estatal,$tasa_especial,$prima_riesgo,$estado,$zona,$region,$res["id_sucursal"]]);
            return $this->crearRespuesta(1,"Sucursal modificada",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
}
