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
            ->where("activo",1)
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
    public function obtenerDetalleSolicitudBaja($id_movimiento)
    {
        $detalle_baja = DB::table('rh_detalle_baja as rdb')
        ->select(DB::raw('CONCAT(rcc.apellido_paterno, " ", rcc.apellido_materno, " ", rcc.nombre) AS nombre'),"rcc.id_candidato","rdb.fecha_baja","rdb.observaciones as observacion","id_detalle_baja")
        ->join("rh_cat_candidato as rcc","rcc.id_candidato","=","rdb.id_candidato")
        ->where("id_movimiento",$id_movimiento)
        ->where("rdb.activo",1)
        ->get();
        if(count($detalle_baja)>0){
            return $this->crearRespuesta(1,$detalle_baja,200);
        }else{
            return $this->crearRespuesta(2,"No se tiene el detalle de este movimiento",301);
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
    public function modificarDetalleSolicitud(Request $res)
    {
        try{
            $fecha = $this->getHoraFechaActual();
            $usuario_creacion = $res["id_usuario"];
            $candidatos = $res["candidatos"];
            $id_movimiento = $res["id_movimiento"];
            $candidatos_actuales = DB::table('rh_detalle_baja as rdb')
            ->where("id_movimiento",$id_movimiento)
            ->where("activo",1)
            ->get();
            if(count($candidatos_actuales)>0){
                //Existen candidatos en la soli
                foreach($candidatos as $candidato){ //Se recorren los candidatos de la solicitud
                    $band = false;
                    foreach($candidatos_actuales as $candidato_actual){
                        if($candidato_actual->id_candidato == $candidato["id_candidato"]){
                            $band = true;   //El candidato actual si existe en la solicitud
                        }
                    }
                    if(!$band){ //Si no existe en la solicitud
                        $buscarInfoEmpleado = DB::table("rh_movimientos as rm")
                        ->select("dc.id_empresa","dc.id_departamento","dc.id_puesto")
                        ->join("rh_detalle_contratacion as dc","dc.id_movimiento","=","rm.id_movimiento")
                        ->where("dc.id_candidato",$candidato["id_candidato"])
                        ->where("dc.activo",1)
                        ->first();
                        if($buscarInfoEmpleado){
                            $fecha_baja = date("Y-m-d",strtotime($candidato["fecha_baja"]));
                            DB::insert("insert into rh_detalle_baja (id_movimiento, id_candidato, id_empresa, id_departamento, id_puesto, fecha_baja, observaciones, fecha_creacion, usuario_creacion, activo) values (?,?,?,?,?,?,?,?,?,?)",[$id_movimiento,$candidato["id_candidato"],$buscarInfoEmpleado->id_empresa,$buscarInfoEmpleado->id_departamento,$buscarInfoEmpleado->id_puesto,$fecha_baja,$candidato["observacion"],$fecha,$usuario_creacion,1]);
                            $this->cambiarDeEstatus($candidato["id_candidato"],5);
                        }
                    }
                }
                return $this->crearRespuesta(1,"Solicitud de baja modificada con éxito",200);
            }else{
                //Solicitud con candidatos vacios
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
                return $this->crearRespuesta(1,"Solicitud de baja modificada con éxito",200);
            }
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function eliminarDetalle($id_detalle_baja)
    {
        try{
            $recuperar_detalle = DB::table('rh_detalle_baja as rdb')
            ->select("id_candidato","id_movimiento")
            ->where("id_detalle_baja",$id_detalle_baja)
            ->get();
            $validar_detalle = DB::table('rh_detalle_baja as rdb')
            ->where("id_movimiento",$recuperar_detalle[0]->id_movimiento)
            ->where("activo",1)
            ->get()
            ->count();
            if($validar_detalle>1){
                DB::update('update rh_detalle_baja set activo = 0 where id_detalle_baja = ?', [$id_detalle_baja]);
                $this->cambiarDeEstatus($recuperar_detalle[0]->id_candidato,1);
                return $this->crearRespuesta(1,false,200);
            }else{
                //Ultimo detalle de la solicitud
                DB::update('update rh_detalle_baja set activo = 0 where id_detalle_baja = ?', [$id_detalle_baja]);
                $this->cambiarDeEstatus($recuperar_detalle[0]->id_candidato,1);
                DB::update('update rh_movimientos set activo = 0 where id_movimiento = ?', [$recuperar_detalle[0]->id_movimiento]);
                $this->cambiarDeEstatus($recuperar_detalle[0]->id_candidato,1);
                return $this->crearRespuesta(1,true,200);
            }
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function aplicarBaja($id_movimiento)
    {
        try{
            //Actualizar el status del movimiento
            DB::update('update rh_movimientos set id_status = 1 where id_movimiento = ?', [$id_movimiento]);
            //Recuperamos el detalle
            $recuperar_detalle_baja = DB::table('rh_detalle_baja as rdb')
            ->select("id_candidato","rdb.id_puesto","gcp.contratados")
            ->join("gen_cat_puesto as gcp","gcp.id_puesto","=","rdb.id_puesto")
            ->where("rdb.id_movimiento",$id_movimiento)
            ->where("rdb.activo",1)
            ->get();
            if(count($recuperar_detalle_baja)>0){
                foreach($recuperar_detalle_baja as $detalle){
                    $this->cambiarDeEstatus($detalle->id_candidato,2);
                    //Actualizar vacantes
                    $contratados_actual = intval($detalle->contratados) - 1;
                    DB::update('update gen_cat_puesto set contratados = ? where id_puesto = ?', [$contratados_actual,$detalle->id_puesto]);
                }
                return $this->crearRespuesta(1,"Baja aplicada con éxito",200);
            }else{
                return $this->crearRespuesta(2,"No se pudo recuperar los candidatos",301);
            }
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
}
