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
        $movientos_alta = DB::table("rh_movimientos as rm")
        ->select("rm.id_movimiento","rm.tipo_movimiento as id_movimiento","rm.tipo_movimiento","rm.fecha_movimiento","gcu.nombre")
        ->join("rh_detalle_contratacion as rdc","rdc.id_movimiento","=","rm.id_movimiento")
        ->join("gen_cat_usuario as gcu","gcu.id_usuario","=","rm.usuario_creacion")
        ->where("rdc.id_empresa",$id_empresa)
        ->where("rdc.activo",1)
        ->where("rm.id_status",1)
        ->orderBy("rm.fecha_movimiento","DESC")
        ->take(10)
        ->get();
        $movientos_mod = DB::table("rh_movimientos as rm")
        ->select("rm.id_movimiento","rm.tipo_movimiento as id_movimiento","rm.tipo_movimiento","rm.fecha_movimiento","gcu.nombre")
        ->join("rh_detalle_modificacion as rdm","rdm.id_movimiento","=","rm.id_movimiento")
        ->join("gen_cat_usuario as gcu","gcu.id_usuario","=","rm.usuario_creacion")
        ->where("rdm.id_empresa",$id_empresa)
        ->where("rdm.activo",1)
        ->where("rm.id_status",1)
        ->orderBy("rm.fecha_movimiento","DESC")
        ->take(10)
        ->get();
        $movientos_baja = DB::table("rh_movimientos as rm")
        ->select("rm.id_movimiento","rm.tipo_movimiento as id_movimiento","rm.tipo_movimiento","rm.fecha_movimiento","gcu.nombre")
        ->join("rh_detalle_baja as rdb","rdb.id_movimiento","=","rm.id_movimiento")
        ->join("gen_cat_usuario as gcu","gcu.id_usuario","=","rm.usuario_creacion")
        ->where("rdb.id_empresa",$id_empresa)
        ->where("rdb.activo",1)
        ->where("rm.id_status",1)
        ->orderBy("rm.fecha_movimiento","DESC")
        ->take(10)
        ->get();
        $solicitudes_empresa = DB::table("rh_movimientos as rm")
        ->select("rm.id_movimiento","rm.tipo_movimiento","rm.fecha_movimiento","gcp.puesto")
        ->join("rh_detalle_contratacion as rdc","rdc.id_movimiento","=","rm.id_movimiento")
        ->join("gen_cat_puesto as gcp","gcp.id_puesto","=","rdc.id_puesto")
        ->where("id_empresa",$id_empresa)
        ->where("rm.id_status",5)
        ->where("rm.activo",1)
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
            "tabla_mov" => []
        ];
        foreach($movientos_alta as $moviento){
            $moviento->id_movimiento = 1;
            array_push($arreglo["tabla_mov"],$moviento);
        }
        foreach($movientos_mod as $moviento){
            $moviento->id_movimiento = 3;
            array_push($arreglo["tabla_mov"],$moviento);
        }
        foreach($movientos_baja as $moviento){
            $moviento->id_movimiento = 2;
            array_push($arreglo["tabla_mov"],$moviento);
        }
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
            ->select("rm.id_movimiento","rm.tipo_movimiento as id_movimiento","rm.tipo_movimiento",DB::raw('DATE_FORMAT(rm.fecha_movimiento,"%d-%m-%Y") as fecha_movimiento'),"gcu.nombre")
            ->join("gen_cat_usuario as gcu","gcu.id_usuario","=","rm.usuario_creacion")
            ->where("rm.id_cliente",$id_cliente)
            ->where("rm.activo",1)
            ->where("rm.id_status",1)
            ->orderBy("rm.fecha_movimiento","DESC")
            ->take(10)
            ->get();
            $movientos_por_procesar = DB::table("rh_movimientos as rm")
            ->select("rm.id_movimiento","rm.tipo_movimiento","rm.fecha_movimiento","gcu.nombre")    
            ->join("gen_cat_usuario as gcu","gcu.id_usuario","=","rm.usuario_creacion")
            ->where("rm.id_cliente",$id_cliente)
            ->whereIn("rm.id_status",[8,9])
            ->where("rm.activo",1)
            ->orderBy("rm.fecha_movimiento","DESC")
            ->take(10)
            ->get();
            $puestos_empresa = DB::table("gen_cat_departamento as gcd")
            ->select("gcp.puesto","gcp.autorizados","gcp.id_puesto","gcp.puesto as contratados")
            ->join("gen_cat_puesto as gcp","gcp.id_departamento","=","gcd.id_departamento")
            ->whereIn("gcd.id_empresa",$id_empresas)
            ->take(10)
            ->get();
            $autorizados = 0;
            $contratados = 0;
            foreach($puestos_empresa as $puesto){
                $puesto->contratados = $this->obtenerContratados($puesto->id_puesto);
                $puesto->vacantes = intval($puesto->autorizados) - intval($puesto->contratados);
                if($puesto->contratados != null){
                    $contratados = $contratados + intval($puesto->contratados);
                }else{
                    $puesto->contratados = 0;
                }
                $autorizados = $autorizados + intval($puesto->autorizados);
            }
            foreach($movientos as $moviento){
                if($moviento->id_movimiento == "A"){
                    $moviento->id_movimiento = 1;
                }
                if($moviento->id_movimiento == "B"){
                    $moviento->id_movimiento = 2;
                }
                if($moviento->id_movimiento == "M"){
                    $moviento->id_movimiento = 3;
                }
            }
            $vacantes = $autorizados - $contratados;
            $arreglo = [
                "targetas" => [
                    "activos" => $contratados,
                    "autorizados" => $autorizados,
                    "vacantes"=> $vacantes,
                    "por_procesar" => count($movientos_por_procesar)
                ],
                "tabla_puesto" => $puestos_empresa,
                "tabla_mov" => $movientos
            ];
            return $this->crearRespuesta(1,$arreglo,200);
        }
    }
    public function obtenerDashboardNomina($id_empresa){
        // $respuesta = [
        //     "empleados_activos" => ,
        //     "pero"
        // ]
    }
    public function obtenerDasboardFacturacion(Request $res)
    {
        $id_cliente = $res["id_cliente"];
        $anio = $res["anio"];
        $empresas = DB::table('liga_empresa_cliente as lec')
        ->select("lec.id_empresa","ce.empresa as nombre","ce.razon_social","representante_legal as repre")
        ->join("gen_cat_empresa as ce","ce.id_empresa","=","lec.id_empresa")
        ->where("lec.id_cliente",$id_cliente)
        ->get();
        if(count($empresas)>0){
            $respuesta = [
                "empresas_t" => count($empresas),
                "total" => 0,
                "pagadas_t" => 0,
                "por_pagar_t" => 0,
                "empresas" => ""
            ];
            $empresas_array = [];
            foreach($empresas as $empresa){
                $facturas = DB::table('fac_factura')
                ->select(DB::raw('YEAR(fecha_creacion) as anio'),"pagado")
                ->where("id_empresa",$empresa->id_empresa)
                ->where(DB::raw('YEAR(fecha_creacion)'),$anio)
                ->get();
                $respuesta["total"] += count($facturas);
                $pagadas = 0;
                $por_pagar = 0;
                foreach($facturas as $factura){
                    if($factura->pagado == 0){
                        $respuesta["por_pagar_t"]++;
                        $por_pagar++;
                    }else{
                        $respuesta["pagadas_t"]++;
                        $pagadas++;
                    }
                }
                array_push($empresas_array,[
                    "id_empresa" => $empresa->id_empresa,
                    "empresa" => $empresa->nombre,
                    "facturas" => count($facturas),
                    "pagadas" => $pagadas,
                    "por_pagar" => $por_pagar
                ]);
            }
            
            $respuesta["empresas"] = $empresas_array;
            return $this->crearRespuesta(1,$respuesta,200);
        }
    }
    public function obtenerDatosEmpresaFacturacion(Request $res)
    {
        $id_empresa = $res["id_empresa"];
        $anio = $res["anio"];
        $respuesta = [];
        for($i=1;$i<=12;$i++){
            $facturas = DB::table('fac_factura')
            ->where("id_empresa",$id_empresa)
            ->where(DB::raw("YEAR(fecha_creacion)"),$anio)
            ->where(DB::raw("MONTH(fecha_creacion)"),$i)
            ->get();
            $pagadas = 0;
            $por_pagar = 0;
            foreach($facturas as $factura){
                if($factura->pagado == 0){
                    $por_pagar++;
                }else{
                    $pagadas++;
                }
            }
            array_push($respuesta,[
                "mes" => $i,
                "facturas" => count($facturas),
                "pagadas" => $pagadas,
                "por_pagar" => $por_pagar
            ]);
        }
        return $this->crearRespuesta(1,$respuesta,200);
    }
}
