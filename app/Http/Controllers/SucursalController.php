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
        $sucursales = DB::table('nom_sucursales')
        ->where("id_empresa",$id_empresa)
        ->where("activo",1)
        ->get();
        if(count($sucursales)>0){
            return $this->crearRespuesta(1,$sucursales,200);
        }else{
            return $this->crearRespuesta(2,"No se han encontrado sucursales",200);
        }
    }
    public function crearSucursal(Request $res)
    {
        try{
            
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
}
