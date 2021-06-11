<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function obtenerDashboardAdmin($id_empresa)
    {
        $movientos = DB::table("rh_movimientos as rm")
        ->select("rm.id_movimiento","rm.tipo_movimiento","rm.fecha_movimiento","gcu.nombre")
        ->join("rh_detalle_contratacion as rdc","rdc.id_movimiento","=","rm.id_movimiento")
        ->join("gen_cat_usuario as gcu","gcu.id_usuario","=","rm.usuario_creacion")
        ->where("id_empresa",$id_empresa)
        ->orderBy("rm.fecha_movimiento","DESC")
        ->take(10)
        ->get();
        $solicitudes_empresa = DB::table("rh_movimientos as rm")
        ->select("rm.id_movimiento","rm.tipo_movimiento","rm.fecha_movimiento","gcp.puesto")
        ->join("rh_detalle_contratacion as rdc","rdc.id_movimiento","=","rm.id_movimiento")
        ->join("gen_cat_puesto as gcp","gcp.id_puesto","=","rdc.id_puesto")
        ->where("id_empresa",$id_empresa)
        ->where("rm.id_status",5)
        ->get();
        $puestos_empresa = DB::table("liga_empresa_departamento as led")
        ->select("gcp.puesto","gcp.autorizados","gcp.contratados")
        ->join("gen_cat_departamento as gcd","gcd.id_departamento","led.id_departamento")
        ->join("gen_cat_puesto as gcp","gcp.id_departamento","=","gcd.id_departamento")
        ->where("led.id_empresa",$id_empresa)
        ->get();
        $autorizados = 0;
        $contratados = 0;
        foreach($puestos_empresa as $puesto){
            $puesto->vacantes = intval($puesto->autorizados) - intval($puesto->contratados);
            if($contratados != null){
                $contratados = $contratados + intval($puesto->contratados);
            }else{
                $puesto->contratados = 0;
            }
            $autorizados = $autorizados + intval($puesto->autorizados);
        }
        $vacantes = $autorizados - $contratados;
        $arreglo = [
            "targetas" => [
                "activos" => $contratados,
                "autorizados" => $autorizados,
                "vacantes"=> $vacantes,
                "por_procesar" => count($solicitudes_empresa)
            ],
            "tabla_puesto" => $puestos_empresa,
            "tabla_mov" => $movientos
        ];
        return $this->crearRespuesta(1,$arreglo,200);
    }
    public function obtenerDashboardRh($id_cliente)
    {
        $busca_empresas = DB::table("liga_empresa_cliente as lec")
        ->where("lec.id_cliente",$id_cliente)
        ->get();
        if(count($busca_empresas)>0){
            $id_empresas = [];
            foreach($busca_empresas as $id_empresa){
                array_push($id_empresas,$id_empresa->id_empresa);
            }
            $movientos = DB::table("rh_movimientos as rm")
            ->select("rm.id_movimiento","rm.tipo_movimiento","rm.fecha_movimiento","gcu.nombre")
            ->join("rh_detalle_contratacion as rdc","rdc.id_movimiento","=","rm.id_movimiento")
            ->join("gen_cat_usuario as gcu","gcu.id_usuario","=","rm.usuario_creacion")
            ->where("id_cliente",$id_cliente)
            ->orderBy("rm.fecha_movimiento","DESC")
            ->take(10)
            ->get();
            $solicitudes_empresa = DB::table("rh_movimientos as rm")
            ->select("rm.id_movimiento","rm.tipo_movimiento","rm.fecha_movimiento","gcp.puesto")
            ->join("rh_detalle_contratacion as rdc","rdc.id_movimiento","=","rm.id_movimiento")
            ->join("gen_cat_puesto as gcp","gcp.id_puesto","=","rdc.id_puesto")
            ->whereIn("id_empresa",$id_empresas)
            ->where("rm.id_status",5)
            ->get();
            $puestos_empresa = DB::table("liga_empresa_departamento as led")
            ->select("gcp.puesto","gcp.autorizados","gcp.contratados")
            ->join("gen_cat_departamento as gcd","gcd.id_departamento","led.id_departamento")
            ->join("gen_cat_puesto as gcp","gcp.id_departamento","=","gcd.id_departamento")
            ->whereIn("id_empresa",$id_empresas)
            ->get();
            $autorizados = 0;
            $contratados = 0;
            foreach($puestos_empresa as $puesto){
                $puesto->vacantes = intval($puesto->autorizados) - intval($puesto->contratados);
                if($contratados != null){
                    $contratados = $contratados + intval($puesto->contratados);
                }else{
                    $puesto->contratados = 0;
                }
                $autorizados = $autorizados + intval($puesto->autorizados);
            }
            $vacantes = $autorizados - $contratados;
            $arreglo = [
                "targetas" => [
                    "activos" => $contratados,
                    "autorizados" => $autorizados,
                    "vacantes"=> $vacantes,
                    "por_procesar" => count($solicitudes_empresa)
                ],
                "tabla_puesto" => $puestos_empresa,
                "tabla_mov" => $movientos
            ];
            return $this->crearRespuesta(1,$arreglo,200);
        }
    }
    //
}
