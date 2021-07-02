<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BajaController extends Controller
{
    public function obtenerSolicitudesBaja(Request $res)
    {
        try{
            $id_cliente = $res["id_cliente"];
            $fecha_inicio = $res["fecha_inicio"];
            $fecha_fin = $res["fecha_fin"];
            $status = $res["id_status"];
            $str_status = "=";
            if($status == "-1"){
                $str_status = "!=";
            } 
            $busqueda = DB::table("rh_movimientos")
            ->where("id_cliente",$id_cliente)
            ->where("tipo_movimiento","B")
            ->where("id_status",$str_status,$status)
            ->get();
            if(count($busqueda)>0){
                return $this->crearRespuesta(1,$busqueda,200);
            }else{
                return $this->crearRespuesta(2,"No se han encontrado solicitudes de baja",200);
            }
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function crearSolicitudDeBaja(Request $res)
    {
            try{
                $fecha = $this->getHoraFechaActual();
                $usuario_creacion = $res["id_usuario"];
                $candidatos = $res["candidatos"];
                $id_cliente = $res["id_cliente"];
                //Inserta el movimiento en estatus de Solicitud
                $id_movimiento = $this->getSigId("rh_movimientos");
                DB::insert("insert into rh_movimientos (id_movimiento, id_cliente, id_status, fecha_movimiento, tipo_movimiento, usuario_creacion, fecha_creacion, activo) values (?,?,?,?,?,?,?,?)",[$id_movimiento,$id_cliente,5,$fecha,"B",$usuario_creacion,$fecha,1]);
                foreach ($candidatos as $candidato) {
                    $buscarInfoEmpleado = DB::table("rh_movimientos as rm")
                    ->select("dc.id_empresa","dc.id_departamento","dc.id_puesto")
                    ->join("rh_detalle_contratacion as dc","dc.id_movimiento","=","rm.id_movimiento")
                    ->where("dc.id_candidato",$candidato["id_candidato"])
                    ->where("dc.activo",1)
                    ->first();
                    if($buscarInfoEmpleado){
                        $id_detalle_baja = $this->getSigId("rh_detalle_baja");
                        $fecha_baja = date("Y-m-d",strtotime($candidato["fecha_baja"]));
                        DB::insert("insert into rh_detalle_baja (id_detalle_baja, id_movimiento, id_candidato, id_empresa, id_departamento, id_puesto, fecha_baja, observaciones, fecha_creacion, usuario_creacion, activo) values (?,?,?,?,?,?,?,?,?,?,?)",[$id_detalle_baja,$id_movimiento,$candidato["id_candidato"],$buscarInfoEmpleado->id_empresa,$buscarInfoEmpleado->id_departamento,$buscarInfoEmpleado->id_puesto,$fecha_baja,$candidato["observacion"],$fecha,$usuario_creacion,1]);
                        $this->cambiarDeEstatus($candidato["id_candidato"],5);
                    }
                }
                return $this->crearRespuesta(1,"Solicitud de baja creada con éxito",200);
            }catch(Throwable $e){
                return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
            }
    }
}
