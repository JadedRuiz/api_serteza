<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModificacionController extends Controller
{
    public function obtenerModificaciones(Request $res)
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
            $busqueda = DB::table("rh_movimientos as rm")
            ->select(DB::raw('DATE_FORMAT(fecha_movimiento, "%d-%m-%Y") as fecha'),"gcs.status","id_movimiento","rm.id_status")
            ->join("gen_cat_statu as gcs","gcs.id_statu","=","rm.id_status")
            ->where("rm.id_cliente",$id_cliente)
            ->where("rm.tipo_movimiento","M")
            ->where("rm.id_status",$str_status,$status)
            ->where("rm.activo",1)
            ->get();
            if(count($busqueda)>0){
                return $this->crearRespuesta(1,$busqueda,200);
            }else{
                return $this->crearRespuesta(2,"No se han encontrado solicitudes de modificaciones",200);
            }
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function obtenerDetalleModificacion($id_movimiento)
    {
        $detalle_mod = DB::table('rh_detalle_modificacion as rdm')
        ->select(DB::raw('CONCAT(rcc.apellido_paterno, " ", rcc.apellido_materno, " ", rcc.nombre) AS nombre'),"rcc.id_candidato", "rdm.observacion as observacion","id_detalle_modificacion","rdm.id_empresa","rdm.id_departamento","rdm.id_puesto","rdm.id_nomina","rdm.sueldo",DB::raw('DATE_FORMAT(fecha_de_modificacion, "%Y-%m-%d") as fecha_modificacion'),)
        ->join("rh_cat_candidato as rcc","rcc.id_candidato","=","rdm.id_candidato")
        ->where("id_movimiento",$id_movimiento)
        ->where("rdm.activo",1)
        ->get();
        if(count($detalle_mod)>0){
            return $this->crearRespuesta(1,$detalle_mod,200);
        }else{
            return $this->crearRespuesta(2,"No se tiene el detalle de este movimiento",301);
        }
    }
    public function crearSolicitudDeModif(Request $res)
    {
        try{
            $fecha = $this->getHoraFechaActual();
            $usuario_creacion = $res["id_usuario"];
            $candidatos = $res["candidatos"];
            $id_cliente = $res["id_cliente"];
            //Inserta el movimiento en estatus de Solicitud
            $id_movimiento = $this->getSigId("rh_movimientos");
            DB::insert("insert into rh_movimientos (id_movimiento, id_cliente, id_status, fecha_movimiento, tipo_movimiento, usuario_creacion, fecha_creacion, activo) values (?,?,?,?,?,?,?,?)",[$id_movimiento,$id_cliente,5,$fecha,"M",$usuario_creacion,$fecha,1]);
            //Inserta el detalle
            foreach($candidatos as $candidato){
                $id_candidato = $candidato["id_candidato"];
                $id_empresa_mod = $candidato["id_empresa"];
                $id_departamento_mod = $candidato["id_departamento"];
                $id_puesto_mod = $candidato["id_puesto"];
                $id_nomina = $candidato["id_nomina"];
                $sueldo = $candidato["sueldo"];
                $observacion = $candidato["observacion"];
                $fecha_de_mod = date("Y-m-d",strtotime($candidato["fecha_modificacion"]));
                DB::insert('insert into rh_detalle_modificacion (id_movimiento, id_candidato, id_empresa, id_departamento, id_puesto, id_nomina, sueldo, observacion, fecha_de_modificacion, fecha_creacion, usuario_creacion, activo) values (?,?,?,?,?,?,?,?,?,?,?,?)', [$id_movimiento, $id_candidato, $id_empresa_mod, $id_departamento_mod, $id_puesto_mod, $id_nomina, $sueldo, $observacion, $fecha_de_mod, $fecha, $usuario_creacion, 1]);
                $this->cambiarDeEstatus($id_candidato,5);
            }
            return $this->crearRespuesta(1,"Solicitud de modificación creada con éxito",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function modificarDetalleModificacion(Request $res)
    {
        try{
            $fecha = $this->getHoraFechaActual();
            $usuario_creacion = $res["id_usuario"];
            $candidatos = $res["candidatos"];
            $id_movimiento = $res["id_movimiento"];
            foreach($candidatos as $candidato){
                if($candidato["id_detalle_modificacion"] == 0){
                    $id_candidato = $candidato["id_candidato"];
                    $id_empresa_mod = $candidato["id_empresa"];
                    $id_departamento_mod = $candidato["id_departamento"];
                    $id_puesto_mod = $candidato["id_puesto"];
                    $id_nomina = $candidato["id_nomina"];
                    $sueldo = $candidato["sueldo"];
                    $observacion = $candidato["observacion"];
                    $fecha_de_mod = date("Y-m-d",strtotime($candidato["fecha_modificacion"]));
                    DB::insert('insert into rh_detalle_modificacion (id_movimiento, id_candidato, id_empresa, id_departamento, id_puesto, id_nomina, sueldo, observacion, fecha_de_modificacion, fecha_creacion, usuario_creacion, activo) values (?,?,?,?,?,?,?,?,?,?,?,?)', [$id_movimiento, $id_candidato, $id_empresa_mod, $id_departamento_mod, $id_puesto_mod, $id_nomina, $sueldo, $observacion, $fecha_de_mod, $fecha, $usuario_creacion, 1]);
                    $this->cambiarDeEstatus($id_candidato,5);
                }
            }
            return $this->crearRespuesta(1,"Solicitud de modificación editada con éxito",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function eliminarDetalle($id_detalle_modificacion)
    {
        try{
            $recuperar_detalle = DB::table('rh_detalle_modificacion as rdm')
            ->select("id_candidato","id_movimiento")
            ->where("id_detalle_modificacion",$id_detalle_modificacion)
            ->get();
            $validar_detalle = DB::table('rh_detalle_modificacion as rdm')
            ->where("id_movimiento",$recuperar_detalle[0]->id_movimiento)
            ->where("activo",1)
            ->get()
            ->count();
            if($validar_detalle>1){
                DB::update('update rh_detalle_modificacion set activo = 0 where id_detalle_modificacion = ?', [$id_detalle_modificacion]);
                $this->cambiarDeEstatus($recuperar_detalle[0]->id_candidato,1);
                return $this->crearRespuesta(1,false,200);
            }else{
                //Ultimo detalle de la solicitud
                DB::update('update rh_detalle_modificacion set activo = 0 where id_detalle_modificacion = ?', [$id_detalle_modificacion]);
                $this->cambiarDeEstatus($recuperar_detalle[0]->id_candidato,1);
                DB::update('update rh_movimientos set activo = 0 where id_movimiento = ?', [$recuperar_detalle[0]->id_movimiento]);
                $this->cambiarDeEstatus($recuperar_detalle[0]->id_candidato,1);
                return $this->crearRespuesta(1,true,200);
            }
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function aplicarModificacion($id_movimiento)
    {
        try{
            $recuperar_detalle = DB::table('rh_movimientos as rm')
            ->select("rdm.id_candidato")
            ->join("rh_detalle_modificacion as rdm","rdm.id_movimiento","=","rm.id_movimiento")
            ->where("rm.id_movimiento",$id_movimiento)
            ->get();
            if(count($recuperar_detalle)>0){
                // Modificar el status de la solicitud
                DB::update('update rh_movimientos set id_status = 1 where id_movimiento = ?', [$id_movimiento]);
                foreach($recuperar_detalle as $detalle){
                    //Modificar el status de los candidatos
                    $this->cambiarDeEstatus($detalle->id_candidato,1);
                }
                return $this->crearRespuesta(1,"Se ha aplicado el movimiento",200);
            }else{
                return $this->crearRespuesta(2,"Este movimiento no cuenta con detalle",301);
            }
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
}
