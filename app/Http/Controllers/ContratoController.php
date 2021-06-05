<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContratoController extends Controller
{
    public function obtenerMoviemientosContratacion(Request $res)
    {
        $motivos = DB::table('rh_mov_contratacion')
        ->select("id_contratacion","fecha_contratacion")
        ->where("id_cliente",$res["id_cliente"])
        ->get();
        foreach($motivos as $motivo){
            $motivo->fecha_contratacion = date('d-m-Y',strtotime($motivo->fecha_contratacion));
        }
        if(count($motivos)>0){
            return $this->crearRespuesta(1,$motivos,200);
        }else{
            return $this->crearRespuesta(2,"No se han encontrado motivos en este cliente",200);
        }
    }
    public function obtenerMoviemientosContratacionPorId($id_contratacion)
    {
        $detalle_contratacion = DB::table('rh_detalle_contratacion as dc')
        ->select("dc.id_detalle_contratacion","cc.nombre","cc.apellido_paterno","cc.apellido_materno", "cd.departamento","cp.puesto","dc.sueldo","dc.fecha_alta","dc.observacion","dc.id_candidato","dc.id_departamento","dc.id_puesto","ce.empresa","dc.id_empresa","dc.id_nomina")
        ->join("gen_cat_empresa as ce","ce.id_empresa","=","dc.id_empresa")
        ->join("rh_cat_candidato as cc","cc.id_candidato","=","dc.id_candidato")
        ->join("gen_cat_departamento as cd","cd.id_departamento","=","dc.id_departamento")
        ->join("gen_cat_puesto as cp","cp.id_puesto","=","dc.id_puesto")
        ->where("dc.id_contratacion",$id_contratacion)
        ->where("dc.activo",1)
        ->get();
        if(count($detalle_contratacion)>0){
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
            $id_contratacion = $this->getSigId("rh_mov_contratacion");
            $id_cliente = $res["id_cliente"];
            DB::insert('insert into rh_mov_contratacion (id_contratacion, id_cliente, fecha_contratacion, fecha_creacion, usuario_creacion, activo) values (?,?,?,?,?,?)', [$id_contratacion, $id_cliente, $fecha_creacion, $fecha_creacion, $usuario_creacion, 1]);
            //Detalle de contratacion
            $is_good = true;
            foreach($res["detalle_contratacion"] as $detalle){
                $id_detalle_contratacion = $this->getSigId("rh_detalle_contratacion");
                $id_departamento = $detalle["id_departamento"];
                $id_puesto = $detalle["id_puesto"];
                $id_candidato = $detalle["id_candidato"];
                $id_nomina = $detalle["id_nomina"];
                $id_empresa = $detalle["id_empresa"];
                $observacion = $detalle["descripcion"];
                $sueldo = $detalle["sueldo"];
                $fecha_alta = $detalle["fecha_ingreso"];
                 DB::insert('insert into rh_detalle_contratacion (id_detalle_contratacion, id_empresa, id_contratacion, id_departamento, id_puesto, id_candidato, id_nomina, observacion, sueldo, fecha_alta, fecha_creacion, usuario_creacion, activo) values (?,?,?,?,?,?,?,?,?,?,?,?,?)', 
                 [$id_detalle_contratacion, $id_empresa, $id_contratacion, $id_departamento, $id_puesto, $id_candidato,$id_nomina, $observacion, $sueldo, $fecha_alta, $fecha_creacion,$usuario_creacion, 1]);
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
            $busca_candidato = DB::table('detalle_contratacion')
            ->where("id_detalle_contratacion",$id_detalle)
            ->first();
            if($busca_candidato){
                DB::update('update rh_detalle_contratacion set activo = 0, usuario_modificacion = ? where id_detalle_contratacion = ?', [$usuario_creacion, $id_detalle]);
                $validar = $this->cambiarDeEstatus($busca_candidato->id_candidato,6);
                if($validar["ok"]){
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
}
