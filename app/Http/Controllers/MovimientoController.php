<?php

namespace App\Http\Controllers;
use App\Models\Movimiento;
use App\Models\DetalleMov;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MovimientoController extends Controller
{
    public function obtenerMovimientosReclutamiento(Request $res)
    {
        $id_cliente = $res["id_cliente"];
        $movimientos = Movimiento::select("rh_movimientos.id_status","tipo_movimiento","gcs.status","gcu.nombre",DB::raw('DATE_FORMAT(fecha_movimiento,"%d-%m-%Y") as fecha_movimiento'),"id_movimiento","tipo_movimiento as tipo")
        ->join("gen_cat_statu as gcs","gcs.id_statu","=","rh_movimientos.id_status")
        ->join("gen_cat_usuario as gcu","gcu.id_usuario","=","rh_movimientos.usuario_creacion")
        ->where(function($query) use ($res){
            if(isset($res["status"]) && $res["status"] != "-1"){
                $query->where("gcs.id_statu",$res["status"]);
            }
            if(isset($res["tipo"]) && $res["tipo"] != "-1"){
                $query->where("tipo_movimiento",$res["tipo"]);
            }
        })
        ->where("rh_movimientos.id_cliente",$id_cliente)
        ->where("rh_movimientos.activo",1)
        ->get();
        if(count($movimientos)>0){
            foreach($movimientos as $movimiento){
                if($movimiento->tipo_movimiento == "A"){
                    $movimiento->tipo_movimiento = "Alta";
                }
                if($movimiento->tipo_movimiento == "M"){
                    $movimiento->tipo_movimiento = "Modificación";
                }
                if($movimiento->tipo_movimiento == "B"){
                    $movimiento->tipo_movimiento = "Baja";
                }
            }
            return $this->crearRespuesta(1,$movimientos,200);
        }
        return $this->crearRespuesta(2,"No se ha encontrado movimientos con este cliente",200);
    }
    public function obtenerDetallePorId($id_mov)
    {
        $detalles = DB::table('rh_movimientos as rm')
        ->select("rm.id_movimiento as id_registro","rdm.id_detalle","rdm.id_candidato","rdm.id_puesto","gcp.puesto","rdm.id_nomina","gcp.id_departamento","gcd.id_empresa","rdm.sueldo","rdm.sueldo_neto","rdm.fecha_detalle","rdm.observacion as descripcion",DB::raw("CONCAT('(',gce.id_empresa,') ',gce.empresa) as empresa"),DB::raw("CONCAT('(',gcd.id_departamento,') ',gcd.departamento) as departamento"),DB::raw("CONCAT(rcc.apellido_paterno,' ',rcc.apellido_materno,' ', rcc.nombre, ' (',rcc.descripcion,')') as candidato_uno"),DB::raw("CONCAT(rcc.apellido_paterno,' ',rcc.apellido_materno,' ', rcc.nombre) as candidato"),"ncs.sucursal","ncs.id_sucursal","gcp.puesto","gcf.nombre as url_foto")
        ->join("rh_detalle_movimiento as rdm","rdm.id_movimiento","=","rm.id_movimiento")
        ->join("rh_cat_candidato as rcc","rcc.id_candidato","=","rdm.id_candidato")
        ->join("gen_cat_fotografia as gcf","gcf.id_fotografia","=","rcc.id_fotografia")
        ->join("gen_cat_puesto as gcp","gcp.id_puesto","=","rdm.id_puesto")
        ->join("gen_cat_departamento as gcd","gcd.id_departamento","=","gcp.id_departamento")
        ->join("gen_cat_empresa as gce","gce.id_empresa","=","gcd.id_empresa")
        ->join("nom_sucursales as ncs","ncs.id_sucursal","=","rdm.id_sucursal")
        ->where("rm.id_movimiento",$id_mov)
        ->where("rdm.activo",1)
        ->get();
        if(count($detalles)>0){
            $id_registro = 0;   
            foreach($detalles as $detalle){
                $detalle->url_foto = Storage::disk('candidato')->url($detalle->url_foto);
                $detalle->id_registro = $id_registro;
                $id_registro++;
            }
            return $this->crearRespuesta(1,$detalles,200);
        }
        return $this->crearRespuesta(2,"No se ha econtrado el detalle",200);
    }
    public function obtenerDetalleBaja($id_mov)
    {
        $empleados = DB::table('nom_empleados as ne')
        ->select("rcc.id_candidato as id_registro","rdm.id_detalle","rcc.id_candidato",DB::raw("CONCAT('(',gce.id_empresa,')',' ',gce.empresa) as empresa"),DB::raw("CONCAT('(',gcd.id_departamento,')',' ',gcd.departamento) as departamento"),"gcp.puesto","ne.sueldo_diario","ne.sueldo_integrado","ne.id_nomina","sucursal","ne.fecha_ingreso as fecha_detalle","rdm.observacion as descripcion","ncn.nomina",DB::raw("CONCAT(rcc.apellido_paterno,' ',rcc.apellido_materno,' ', rcc.nombre) as candidato"),"rdm.fecha_detalle as fecha_baja")
        ->join("rh_detalle_movimiento as rdm","rdm.id_candidato","=","ne.id_candidato")
        ->join("rh_cat_candidato as rcc","rcc.id_candidato","=","ne.id_candidato")
        ->join("gen_cat_fotografia as gcf","gcf.id_fotografia","=","rcc.id_fotografia")
        ->join("gen_cat_puesto as gcp","gcp.id_puesto","=","ne.id_puesto")
        ->join("gen_cat_departamento as gcd","gcd.id_departamento","=","gcp.id_departamento")
        ->join("gen_cat_empresa as gce","gce.id_empresa","=","gcd.id_empresa")
        ->join("nom_sucursales as ncs","ncs.id_sucursal","=","ne.id_sucursal")
        ->join("nom_cat_nomina as ncn","ncn.id_nomina","=","ne.id_nomina")
        ->where("rdm.id_movimiento",$id_mov)
        ->where("rdm.activo",1)
        ->where("ne.id_estatus",1)
        ->get();
        if(count($empleados)>0){
            $id_registro = 0;   
            foreach($empleados as $empleado){
                $empleado->sueldo_diario = "$".number_format($empleado->sueldo_diario,2,'.',',');
                $empleado->sueldo_integrado = "$".number_format($empleado->sueldo_integrado,2,'.',',');
                $empleado->id_registro = $id_registro;
                $id_registro++;
            }
            return $this->crearRespuesta(1,$empleados,200);
        }
        return $this->crearRespuesta(2,"No se ha encontrado el detalle de la baja",200);
    }
    public function altaMovimiento(Request $res)
    {
        $usuario_creacion = $res["usuario_creacion"];
        $fecha = $this->getHoraFechaActual();
        try{
            $movimiento = new Movimiento();
            $movimiento->id_status = 9;
            $movimiento->id_cliente = $res["id_cliente"];
            $movimiento->fecha_movimiento = $fecha;
            $movimiento->tipo_movimiento = $res["tipo_mov"];
            $movimiento->usuario_creacion = $usuario_creacion;
            $movimiento->fecha_creacion = $fecha;
            $movimiento->activo = 1;
            $movimiento->save();
            $id_mov = $movimiento->id_movimiento;
            foreach($res["movimientos"] as $movimiento_row){
                if($res["tipo_mov"] == "A"){
                    $detalle = new DetalleMov();
                    $detalle->id_movimiento = $id_mov;
                    $detalle->id_status = "5";
                    $detalle->id_candidato = $movimiento_row["id_candidato"];
                    $detalle->id_sucursal = $movimiento_row["id_sucursal"];
                    $detalle->id_puesto = $movimiento_row["id_puesto"];
                    $detalle->id_nomina = $movimiento_row["id_nomina"];
                    $movimiento_row["sueldo"] = str_replace("$","",$movimiento_row["sueldo"]);
                    $movimiento_row["sueldo"] = str_replace(",","",$movimiento_row["sueldo"]);
                    $detalle->sueldo = $movimiento_row["sueldo"];
                    $movimiento_row["sueldo_neto"] = str_replace("$","",$movimiento_row["sueldo_neto"]);
                    $movimiento_row["sueldo_neto"] = str_replace(",","",$movimiento_row["sueldo_neto"]);
                    $detalle->sueldo_neto = $movimiento_row["sueldo_neto"];
                    $detalle->observacion = strtoupper($movimiento_row["descripcion"]);
                    $detalle->fecha_detalle = date('Y-m-d',strtotime($movimiento_row["fecha_detalle"]));
                    $detalle->fecha_creacion = $fecha;
                    $detalle->activo = 1;
                    $detalle->save();
                }
                if($res["tipo_mov"] == "M"){
                    $detalle = new DetalleMov();
                    $detalle->id_movimiento = $id_mov;
                    $detalle->id_status = "5";
                    $detalle->id_candidato = $movimiento_row["id_candidato"];
                    $detalle->id_sucursal = $movimiento_row["id_sucursal"];
                    $detalle->id_puesto = $movimiento_row["id_puesto"];
                    $detalle->id_nomina = $movimiento_row["id_nomina"];
                    $movimiento_row["sueldo"] = str_replace("$","",$movimiento_row["sueldo"]);
                    $movimiento_row["sueldo"] = str_replace(",","",$movimiento_row["sueldo"]);
                    $detalle->sueldo = $movimiento_row["sueldo"];
                    $movimiento_row["sueldo_neto"] = str_replace("$","",$movimiento_row["sueldo_neto"]);
                    $movimiento_row["sueldo_neto"] = str_replace(",","",$movimiento_row["sueldo_neto"]);
                    $detalle->sueldo_neto = $movimiento_row["sueldo_neto"];
                    $detalle->observacion = strtoupper($movimiento_row["descripcion"]);
                    $detalle->fecha_detalle = date('Y-m-d',strtotime($movimiento_row["fecha_detalle"]));
                    $detalle->fecha_creacion = $fecha;
                    $detalle->activo = 1;
                    $detalle->save();
                }
                if($res["tipo_mov"] == "B"){
                    $detalle = new DetalleMov();
                    $detalle->id_status = "5";
                    $detalle->id_movimiento = $id_mov;
                    $detalle->id_candidato = $movimiento_row["id_candidato"];
                    $detalle->observacion = strtoupper($movimiento_row["descripcion"]);
                    $detalle->fecha_detalle = date('Y-m-d',strtotime($movimiento_row["fecha_baja"]));
                    $detalle->fecha_creacion = $fecha;
                    $detalle->activo = 1;
                    $detalle->save();
                }
                $this->cambiarDeEstatus($movimiento_row["id_candidato"],5);
            }
            return $this->crearRespuesta(1,"Los movimientos han sido creados",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function modificarDetalle(Request $res)
    {
        $usuario_creacion = $res["usuario_creacion"];
        $fecha = $this->getHoraFechaActual();
        try{
            if($res["tipo_mov"] == "A"){
                $detalle = DetalleMov::find($res["detalle"]["id_detalle"]);
                $detalle->id_sucursal = $res["detalle"]["id_sucursal"];
                $detalle->id_puesto = $res["detalle"]["id_puesto"];
                $detalle->id_nomina = $res["detalle"]["id_nomina"];
                $sueldo = str_replace("$","",$res["detalle"]["sueldo"]);
                $sueldo = str_replace(",","",$sueldo);
                $detalle->sueldo = $sueldo;
                $sueldo_neto = str_replace("$","",$res["detalle"]["sueldo_neto"]);
                $sueldo_neto = str_replace(",","",$sueldo_neto);
                $detalle->sueldo_neto = $sueldo_neto;
                $detalle->observacion = strtoupper($res["detalle"]["descripcion"]);
                $detalle->fecha_detalle = date('Y-m-d',strtotime($res["detalle"]["fecha_detalle"]));
                $detalle->fecha_creacion = $fecha;
                $detalle->activo = 1;
                $detalle->save();
            }
            if($res["tipo_mov"] == "M"){
                $detalle = new DetalleMov();
                $detalle->id_movimiento = $id_mov;
                $detalle->id_sucursal = $movimiento_row["id_sucursal"];
                $detalle->id_puesto = $movimiento_row["id_puesto"];
                $detalle->id_nomina = $movimiento_row["id_nomina"];
                $movimiento_row["sueldo"] = str_replace("$","",$movimiento_row["sueldo"]);
                $movimiento_row["sueldo"] = str_replace(",","",$movimiento_row["sueldo"]);
                $detalle->sueldo = $movimiento_row["sueldo"];
                $movimiento_row["sueldo_neto"] = str_replace("$","",$movimiento_row["sueldo_neto"]);
                $movimiento_row["sueldo_neto"] = str_replace(",","",$movimiento_row["sueldo_neto"]);
                $detalle->sueldo_neto = $movimiento_row["sueldo_neto"];
                $detalle->observacion = strtoupper($movimiento_row["descripcion"]);
                $detalle->fecha_detalle = date('Y-m-d',strtotime($movimiento_row["fecha_detalle"]));
                $detalle->fecha_creacion = $fecha;
                $detalle->activo = 1;
                $detalle->save();
            }
            if($res["tipo_mov"] == "B"){
                $id_mov = $movimiento->id_movimiento;
                $detalle = new DetalleMov();
                $detalle->id_movimiento = $id_mov;
                $detalle->id_puesto = $movimiento_row["id_puesto"];
                $detalle->observacion = strtoupper($movimiento_row["descripcion"]);
                $detalle->fecha_detalle = date('Y-m-d',strtotime($movimiento_row["fecha_detalle"]));
                $detalle->fecha_creacion = $fecha;
                $detalle->activo = 1;
                $detalle->save();
            }
            $this->cambiarDeEstatus($res["detalle"]["id_candidato"],5);
            return $this->crearRespuesta(1,"El movimiento ha sido modificado",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function modificarMovimiento(Request $res)
    {
        $usuario_creacion = $res["usuario_creacion"];
        $fecha = $this->getHoraFechaActual();
        $id_movimiento = $res["id_movimiento"];
        try{
            foreach($res["movimientos"] as $movimiento_row){
                if($movimiento_row["id_detalle"] != "0"){
                    $detalle = DetalleMov::find($movimiento_row["id_detalle"]);
                    $detalle->fecha_modificacion = $fecha;
                    $detalle->usuario_modificacion = $usuario_creacion;
                }else{
                    $detalle = new DetalleMov();
                    $detalle->id_movimiento = $id_movimiento;
                    $detalle->id_status = "5";
                    $detalle->fecha_creacion = $fecha;
                    $detalle->usuario_creacion = $usuario_creacion;
                }
                if($res["tipo_mov"] == "A" || $res["tipo_mov"] == "M"){
                    $detalle->id_candidato = $movimiento_row["id_candidato"];
                    $detalle->id_sucursal = $movimiento_row["id_sucursal"];
                    $detalle->id_puesto = $movimiento_row["id_puesto"];
                    $detalle->id_nomina = $movimiento_row["id_nomina"];
                    $movimiento_row["sueldo"] = str_replace("$","",$movimiento_row["sueldo"]);
                    $movimiento_row["sueldo"] = str_replace(",","",$movimiento_row["sueldo"]);
                    $detalle->sueldo = $movimiento_row["sueldo"];
                    $movimiento_row["sueldo_neto"] = str_replace("$","",$movimiento_row["sueldo_neto"]);
                    $movimiento_row["sueldo_neto"] = str_replace(",","",$movimiento_row["sueldo_neto"]);
                    $detalle->sueldo_neto = $movimiento_row["sueldo_neto"];
                    $detalle->observacion = strtoupper($movimiento_row["descripcion"]);
                    $detalle->fecha_detalle = date('Y-m-d',strtotime($movimiento_row["fecha_detalle"]));
                    $detalle->activo = 1;
                }
                if($res["tipo_mov"] == "B"){
                    $detalle->id_candidato = $movimiento_row["id_candidato"];
                    $detalle->observacion = strtoupper($movimiento_row["descripcion"]);
                    $detalle->fecha_detalle = date('Y-m-d',strtotime($movimiento_row["fecha_baja"]));
                    $detalle->fecha_creacion = $fecha;
                    $detalle->activo = 1;
                    $detalle->save();
                }
                $detalle->save();
                $this->cambiarDeEstatus($movimiento_row["id_candidato"],5);
            }
            return $this->crearRespuesta(1,"Los movimientos han sido creados",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function cancelarMovimiento($id_mov)
    {
        try{
            $detalles = DB::table('rh_movimientos as rm')
            ->select("id_detalle","id_candidato","tipo_movimiento")
            ->join("rh_detalle_movimiento as rdm","rdm.id_movimiento","=","rm.id_movimiento")
            ->where("rm.id_movimiento",$id_mov)
            ->get();
            if(count($detalles)>0){
                foreach($detalles as $detalle){
                    DB::update('update rh_detalle_movimiento set activo = 0 where id_detalle = ?', [$detalle->id_detalle]);
                    if($detalle->tipo_movimiento == "A"){
                        $this->cambiarDeEstatus($detalle->id_candidato,6);
                    }
                    if($detalle->tipo_movimiento == "B"){
                        $this->cambiarDeEstatus($detalle->id_candidato,1);
                    }
                    if($detalle->tipo_movimiento == "M"){
                        $this->cambiarDeEstatus($detalle->id_candidato,1);
                    }
                }
            }
            DB::update('update rh_movimientos set activo = 0 where id_movimiento = ?', [$id_mov]);
            return $this->crearRespuesta(1,"Movimiento cancelado",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function cancelarDetalle($id_detalle)
    {
        try{
            $detalle = DB::table('rh_movimientos as rm')
            ->select("id_detalle","id_candidato","tipo_movimiento")
            ->join("rh_detalle_movimiento as rdm","rdm.id_movimiento","=","rm.id_movimiento")
            ->where("rdm.id_detalle",$id_detalle)
            ->first();
            if($detalle){
                if($detalle->tipo_movimiento == "A"){
                    $this->cambiarDeEstatus($detalle->id_candidato,6);
                }
                if($detalle->tipo_movimiento == "B"){
                    $this->cambiarDeEstatus($detalle->id_candidato,1);
                }
                if($detalle->tipo_movimiento == "M"){
                    $this->cambiarDeEstatus($detalle->id_candidato,1);
                }
                DB::update('update rh_detalle_movimiento set activo = 0 where id_detalle = ?', [$id_detalle]);
                return $this->crearRespuesta(1,"Se ha cancelado el detalle",200);
            }
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function cambiarStatusMov($id_status,$id_mov)
    {
        try{
            DB::update('update rh_movimientos set id_status = ? where id_movimiento = ?', [$id_status,$id_mov]);
            return $this->crearRespuesta(1,"Se ha cambiado el estatus",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function aplicarMovimiento(Request $res)
    {
        $id_movimiento = $res["id_movimiento"];
        $fecha = $this->getHoraFechaActual();
        $detalles = DB::table('rh_detalle_movimiento as rdm')
        ->select("id_detalle","id_nomina","rdm.id_puesto","rdm.id_candidato","sueldo","sueldo_neto","id_sucursal","fecha_detalle","gcp.puesto", DB::raw("CONCAT(rcc.nombre, ' ', rcc.apellido_paterno, ' ', rcc.apellido_materno) as candidato"),"gcd.id_empresa","observacion")
        ->leftJoin("gen_cat_puesto as gcp","gcp.id_puesto","=","rdm.id_puesto")
        ->join("rh_cat_candidato as rcc","rcc.id_candidato","=","rdm.id_candidato")
        ->leftJoin("gen_cat_departamento as gcd","gcd.id_departamento","=","gcp.id_departamento")
        ->where("id_movimiento",$id_movimiento)
        ->where("rdm.activo",1)
        ->get();
        $errores = [];
        $cortar = false;
        $tipo = 0;
        foreach($detalles as $detalle){
            if($res["tipo_mov"] == "B"){
                $validar_exist = Empleado::select("id_empleado")->where("id_candidato",$detalle->id_candidato)
                ->first();
                if($validar_exist){
                    $empleado  = Empleado::find($validar_exist->id_empleado);
                    $empleado->id_estatus = 2;
                    $empleado->save();
                    $this->cambiarDeEstatus($detalle->id_candidato,2);
                    $this->cambiarStatusDetalle(1,$detalle->id_detalle);
                }else{
                    $this->cambiarStatusDetalle(5,$detalle->id_detalle);
                    array_push($errores,"El empleado con nombre '".$detalle->candidato."' no se ha encontrado en nuestras base de datos");
                }
            }else{
                if($this->estaElPuestoDisponible($detalle->id_puesto)){
                    $id_status = 0;
                    if($res["tipo_mov"] == "A"){
                        $empleado = new Empleado();
                        $id_status = 1;
                        $validar_exist = Empleado::where("id_candidato",$detalle->id_candidato)
                        ->first();
                        $cortar = false;
                        $tipo = 0;
                        if($validar_exist){
                            $cortar = true;
                        }
                        $empleado->id_candidato = $detalle->id_candidato;
                        $empleado->id_registropatronal = 0;
                        $empleado->id_catbanco = 0;
                        $empleado->id_contratosat = 0;
                        $empleado->folio = $this->getSigIdEmpresa($detalle->id_empresa);
                        $empleado->fecha_antiguedad = date('Y-m-d',strtotime($detalle->fecha_detalle));
                        $empleado->cuenta = "";
                        $empleado->tarjeta = "";
                        $empleado->clabe = "";
                        $empleado->tipo_salario = "F";
                        $empleado->jornada = "N";
                        $empleado->sueldo_complemento = 0.00;
                        $empleado->aplicarsueldoneto = 0;
                        $empleado->sinsubsidio = 0;
                        $empleado->prestaciones_antiguedad = 0;
                        $empleado->usuario_creacion = $res["usuario_creacion"];
                        $empleado->fecha_creacion = $fecha;
                    }
                    if($res["tipo_mov"] == "M"){
                        $validar_exist = Empleado::select("id_empleado")->where("id_candidato",$detalle->id_candidato)
                        ->first();
                        $cortar = true;
                        $tipo = 1;
                        $id_status = 1;
                        if($validar_exist){
                            $empleado = Empleado::find($validar_exist->id_empleado);
                            $cortar = false;
                            $empleado->usuario_modificacion = $res["usuario_creacion"];
                            $empleado->fecha_modificacion = $fecha;
                        }
                    }
                    if(!$cortar){
                        $empleado->id_estatus = $id_status;
                        $empleado->id_nomina = $detalle->id_nomina;
                        $empleado->id_puesto = $detalle->id_puesto;
                        $empleado->id_sucursal = $detalle->id_sucursal;
                        $empleado->sueldo_diario = $detalle->sueldo;
                        $empleado->sueldo_integrado = $detalle->sueldo_neto;
                        $empleado->fecha_ingreso = date('Y-m-d',strtotime($detalle->fecha_detalle));
                        $empleado->descripcion = $detalle->observacion;
                        $empleado->save();
                        $this->cambiarDeEstatus($detalle->id_candidato,1);
                        $this->cambiarStatusDetalle(1,$detalle->id_detalle);
                    }else{
                        $this->cambiarStatusDetalle(5,$detalle->id_detalle);
                        if($tipo == 0){
                            array_push($errores,"El candidato "+$detalle->candidato." ya se encuentra contratato en una empresa");
                        }
                        if($tipo == 1){
                            array_push($errores,"El empleado que se desea modificar no se encuentra en la lista de empleados de está empresa");
                        }
                    }
                }else{
                    $this->cambiarStatusDetalle(5,$detalle->id_detalle);
                    array_push($errores,"El puesto '".$detalle->puesto."' asignado a ".$detalle->candidato." ya ha alcanzado el máximo de puestos autorizados.");
                }
            }
        }
        if(count($errores)>0){
            return $this->crearRespuesta(1,[
                "tipo" => 2,
                "errores" => $errores 
            ],200);
        }
        $this->cambiarStatusMov(1,$id_movimiento);
        return $this->crearRespuesta(1,[
            "tipo" => 1,
            "data" => "Se ha aplicado la precaptura"
        ],200);
    }
    public function cambiarStatusDetalle($id_status, $id_detalle)
    {
        try{
            DB::update('update rh_detalle_movimiento set id_status = ? where id_detalle = ?', [$id_status,$id_detalle]);
            return $this->crearRespuesta(1,"Se ha cambiado el estatus",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
}

