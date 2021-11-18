<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\ContratoExport;

class ContratoController extends Controller
{
    public function obtenerMoviemientosContratacion(Request $res)
    {
        try{
            $fecha_inicial = $res["fecha_inicio"];
            $fecha_final = $res["fecha_final"];
            $status = $res["id_status"];
            $otro = "=";
            if($status == "-1"){
                $otro = "!=";
            }
            $motivos = DB::table('rh_movimientos as rm')
            ->select("rm.id_movimiento","rm.fecha_movimiento","rm.id_status","gcs.status")
            ->where("rm.id_status",$otro,$status)
            ->where("tipo_movimiento","A")
            ->where("rm.id_cliente",$res["id_cliente"])
            ->where(function ($query) use ($fecha_inicial,$fecha_final){
                if($fecha_final != "" && $fecha_inicial != ""){
                    $query->whereBetween("rm.fecha_movimiento",[$fecha_inicial,$fecha_final]);
                }
            })
            ->where("rm.activo",1)
            ->join("gen_cat_statu as gcs","gcs.id_statu","=","rm.id_status")
            ->get();
            foreach($motivos as $motivo){
                $motivo->fecha_movimiento = date('d-m-Y',strtotime($motivo->fecha_movimiento));
            }
            if(count($motivos)>0){
                return $this->crearRespuesta(1,$motivos,200);
            }
            return $this->crearRespuesta(2,"No se han encontrado motivos en este cliente",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function obtenerMoviemientosContratacionPorId($id_movimiento)
    {
        $detalle_contratacion = DB::table('rh_detalle_contratacion as dc')
        ->select("dc.id_detalle_contratacion",DB::raw('CONCAT(cc.apellido_paterno," ",cc.apellido_materno, " ",cc.nombre) as nombre'), "cd.departamento","cp.puesto","dc.sueldo", "dc.sueldo_neto", "dc.fecha_alta","dc.observacion","dc.id_candidato","dc.id_departamento","dc.id_puesto","ce.empresa","dc.id_empresa","dc.id_nomina","rm.id_status","dc.id_sucursal", "ns.sucursal","ncn.nomina")
        ->join("rh_movimientos as rm","rm.id_movimiento","=","dc.id_movimiento")
        ->join("gen_cat_empresa as ce","ce.id_empresa","=","dc.id_empresa")
        ->join("rh_cat_candidato as cc","cc.id_candidato","=","dc.id_candidato")
        ->join("gen_cat_departamento as cd","cd.id_departamento","=","dc.id_departamento")
        ->join("gen_cat_puesto as cp","cp.id_puesto","=","dc.id_puesto")
        ->join("nom_cat_nomina as ncn","ncn.id_nomina","=","dc.id_nomina")
        ->join("nom_sucursales as ns","ns.id_sucursal","=","dc.id_sucursal")
        ->where("dc.id_movimiento",$id_movimiento)
        ->where("dc.activo",1)
        ->get();
        if(count($detalle_contratacion)>0){
            foreach($detalle_contratacion as $detalle){
                $detalle->sueldo = "$" . number_format($detalle->sueldo,2,".",",");
            }
            return $this->crearRespuesta(1,$detalle_contratacion,200);
        }else{
            return $this->crearRespuesta(2,"No se ha encontrado el detalle de este movimiento de contratación",200);
        }
    }
    public function altaMovContrato(Request $res)
    {
        try{
            $fecha_creacion = $this->getHoraFechaActual();
            $usuario_creacion = $res["usuario_creacion"];
            //Movimiento de contratacion
            $id_movimiento = $this->getSigId("rh_movimientos");
            $id_cliente = $res["id_cliente"];
            DB::insert('insert into rh_movimientos (id_movimiento, id_cliente, id_status, tipo_movimiento, fecha_movimiento, fecha_creacion, usuario_creacion, activo) values (?,?,?,?,?,?,?,?)', [$id_movimiento, $id_cliente, 5, "A", $fecha_creacion, $fecha_creacion, $usuario_creacion, 1]);
            //Detalle de contratacion
            $is_good = true;
            foreach($res["detalle_contratacion"] as $detalle){
                $id_departamento = $detalle["id_departamento"];
                $id_puesto = $detalle["id_puesto"];
                $id_candidato = $detalle["id_candidato"];
                $id_nomina = $detalle["id_nomina"];
                $id_empresa = $detalle["id_empresa"];
                $id_sucursal = $detalle["id_sucursal"];
                $observacion = $detalle["descripcion"];
                $sueldo_neto = $detalle["sueldo_neto"];
                $sueldo = $detalle["sueldo"];
                $fecha_alta = $detalle["fecha_ingreso"];
                 DB::insert('insert into rh_detalle_contratacion (id_empresa, id_sucursal, id_movimiento, id_departamento, id_puesto, id_candidato, id_nomina, observacion, sueldo, sueldo_neto, fecha_alta, fecha_creacion, usuario_creacion, activo) values (?,?,?,?,?,?,?,?,?,?,?,?,?,?)', 
                 [$id_empresa, $id_sucursal, $id_movimiento, $id_departamento, $id_puesto, $id_candidato,$id_nomina, $observacion, $sueldo, $sueldo_neto, $fecha_alta, $fecha_creacion,$usuario_creacion, 1]);
                 $validar = $this->cambiarDeEstatus($id_candidato,5);
                 if(!$validar["ok"]){
                    $is_good = false;
                 }
            }
            if($is_good){
                return $this->crearRespuesta(1,"El movimiento de contratacion ha salido con éxito",200);
            }else{
                return $this->crearRespuesta(2,"Ha ocurrido un error al actualizar el status del candidato.",200);
            }
            
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function editarMovContrato(Request $res){
        try{
            $fecha_modificacion = $this->getHoraFechaActual();
            $usuario_modificacion = $res["usuario_creacion"];
            //Detalle de contratacion
            $is_good = true;
            foreach($res["detalle_contratacion"] as $detalle){
                $id_detalle_contratacion = $detalle["id_detalle"];
                $id_departamento = $detalle["id_departamento"];
                $id_puesto = $detalle["id_puesto"];
                $id_candidato = $detalle["id_candidato"];
                $id_nomina = $detalle["id_nomina"];
                $id_empresa = $detalle["id_empresa"];
                $id_sucursal = $detalle["id_sucursal"];
                $observacion = $detalle["descripcion"];
                $sueldo = $detalle["sueldo"];
                $sueldo_neto = $detalle["sueldo_neto"];
                $fecha_alta = $detalle["fecha_ingreso"];
                 DB::update('update rh_detalle_contratacion set id_empresa = ?, id_sucursal = ?, id_departamento = ?, id_puesto = ?, id_candidato = ?, id_nomina = ?, observacion = ?, sueldo = ?, sueldo_neto = ?, fecha_alta = ?, fecha_modificacion = ?, usuario_modificacion = ? where id_detalle_contratacion = ?', 
                 [$id_empresa, $id_sucursal, $id_departamento, $id_puesto, $id_candidato,$id_nomina, $observacion, $sueldo, $sueldo_neto, $fecha_alta, $fecha_modificacion,$usuario_modificacion,$id_detalle_contratacion]);
                 $validar = $this->cambiarDeEstatus($id_candidato,5);
                 if(!$validar["ok"]){
                    $is_good = false;
                 }
            }
            if($is_good){
                return $this->crearRespuesta(1,"El movimiento de contratacion ha salido con éxito",200);
            }else{
                return $this->crearRespuesta(2,"Ha ocurrido un error al actualizar el status del candidato.",200);
            }
            
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function eliminarDetalle(Request $res)
    {
        try{
            $usuario_creacion = $res["usuario_creacion"];
            $id_detalle = $res["id_detalle"];
            $fecha = $this->getHoraFechaActual();
            $busca_candidato = DB::table('rh_detalle_contratacion')
            ->where("id_detalle_contratacion",$id_detalle)
            ->first();
            if($busca_candidato){
                DB::update('update rh_detalle_contratacion set activo = 0, usuario_modificacion = ? where id_detalle_contratacion = ?', [$usuario_creacion, $id_detalle]);
                $validar = $this->cambiarDeEstatus($busca_candidato->id_candidato,6);
                if($validar["ok"]){
                    $valida_detalles = DB::table("rh_detalle_contratacion as dc")
                    ->where("dc.id_movimiento",$busca_candidato->id_movimiento)
                    ->where("activo",1)
                    ->get();
                    if(count($valida_detalles)==0){
                        //Se han eliminado todos lo candidatos del mov
                        DB::update('update rh_movimientos set activo = 0, usuario_modificacion = ?, fecha_modificacion = ? where id_movimiento = ?',[$usuario_creacion,$fecha,$busca_candidato->id_movimiento]);
                        return $this->crearRespuesta(1,"true",200);
                    }
                    return $this->crearRespuesta(1,"Se ha eliminado con éxito",200);
                }else{
                    return $this->crearRespuesta(2,$validar["message"],200);
                }
            }else{
                return $this->crearRespuesta(2,"No se ha podido eliminar, intente de nuevo.",200);
            }
            
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
        
    }
    public function obtenerCatalogoNomina()
    {
        $nominas = DB::table('nom_cat_nomina')
        ->select("id_nomina","nomina")
        ->get();
        if(count($nominas)>0){
            return $this->crearRespuesta(1,$nominas,200);
        }
        return $this->crearRespuesta(2,"No se ha encontrar el catálogo de nóminas",200);
    }
    public function aplicarContratacion($id_movimiento,$usuario_creacion)
    {
        try{
            $validar_status = DB::table("rh_movimientos as rm")
            ->select("id_status")
            ->where("id_movimiento",$id_movimiento)
            ->where("id_status",5)
            ->get();
            if(count($validar_status)>0){
                $fecha = $this->getHoraFechaActual();
                $detalle_contratacion = DB::table("rh_detalle_contratacion as rdc")
                ->select("rdc.id_detalle_contratacion","gcp.contratados","rdc.id_puesto","rdc.id_candidato")
                ->join("gen_cat_puesto as gcp","gcp.id_puesto","=","rdc.id_puesto")
                ->where("rdc.id_movimiento",$id_movimiento)
                ->get();
                if(count($detalle_contratacion)>0){
                    foreach ($detalle_contratacion as $detalle) {
                        //aumenta el bit contratados del puesto
                        $contratados = 0;
                        if($detalle->contratados != null){
                            $contratatos = intval($detalle->contratados);
                        }
                        DB::update('update gen_cat_puesto set contratados = ?, fecha_creacion = ?, usuario_modificacion = ? where id_puesto= ?',[($contratados+1),$fecha,$usuario_creacion,$detalle->id_puesto]);
                        //Actualiza el status del candidato
                        $this->cambiarDeEstatus($detalle->id_candidato,1);
                    }
                    DB::update('update rh_movimientos set id_status = 1, fecha_creacion = ?, usuario_modificacion = ? where id_movimiento = ?',[$fecha,$usuario_creacion,$id_movimiento]);
                    return $this->crearRespuesta(1,"La solicitud se ha aplicado con éxito",200);
                }
                return $this->crearRespuesta(2,"El movimiento no cuenta con un detalle",301);
            }
            return $this->crearRespuesta(2,"El movimiento ya ha sido aplicado",301);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function obtenerDocContratacion(Request $res)
    {
        $contrato = new ContratoExport();
        return $contrato->obtenerContrato(-1);
    }
}
