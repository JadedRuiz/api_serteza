<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class ContratoController extends Controller
{
    public function obtenerMoviemientosContratacion(Request $res)
    {
        $empresas_por_clientes = DB::table('liga_empresa_cliente')
        ->where("id_usuario_cliente",$res["id_cliente"])
        ->get();
        $ids_empresa_cliente = [];
        foreach($empresas_por_clientes as $empresa_cliente){
            array_push($ids_empresa_cliente,$empresa_cliente->id_empresa_cliente);
        }
        $motivos = DB::table('mov_contratacion')
        ->select("id_contratacion","fecha_contrato", "status")
        ->whereIn("id_empresa_cliente",$ids_empresa_cliente)
        ->get();
        if(count($motivos)>0){
            return $this->crearRespuesta(1,$motivos,200);
        }else{
            return $this->crearRespuesta(2,"No se han encontrado motivos en este cliente",200);
        }
    }
    public function altaMovContrato(Request $res)
    {
        try{
            $fecha_creacion = $this->getFechaHoraActual();
            $usuario_creacion = $res["usuario_creacion"];
            //Movimiento de contratacion
            $id_contratacion = $this->getSigId("mov_contratacion");
            $id_empresa_cliente = $res["id_empresa_cliente"];
            DB::insert('insert into mov_contratacion (id_contratacion, id_empresa_cliente, fecha_contratacion, fecha_creacion, usuario_creacion) values (?,?,?,?,?)', [-$id_contratacion, $id_empresa_cliente, $fecha_creacion, $fecha_creacion, $usuario_creacion]);
            //Detalle de contratacion
            foreach($res["detalle_contratacion"] as $detalle){
                $id_detalle_contratacion = $this->getSigId("detalle_contratacion");
                $id_departamento = $detalle["id_departamento"];
                $id_puesto = $detalle["id_puesto"];
                $id_candidato = $detalle["id_candidato"];
                $observacion = $detalle["observacion"];
                $sueldo = $detalle["sueldo"];
                $fecha_alta = $detalle["fecha_alta"];
                 DB::insert('insert into users (id_detalle_contratacion, id_contratacion, id_departamento, id_puesto, id_candidato, observacion, sueldo, fecha_alta, fecha_creacion, usuario_creacion) values (?,?,?,?,?,?,?,?,?,?)', 
                 [$id_detalle_contratacion, $id_contratacion, $id_departamento, $id_puesto, $id_candidato, $observacion, $sueldo, $fecha_alta, $fecha_creacion,$usuario_creacion]);
            }
            return $this->crearRespuesta(1,"El movimiento de contratacion ha salido con éxito",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    

}
