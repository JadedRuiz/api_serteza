<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Candidato;
use App\Models\Cotizacion;
use App\Models\DetCotizacion;

class NominaController extends Controller
{
    public function obtenerNombreNominaPorId($id_nomina)
    {
        $nombre_nomina = DB::table('nom_cat_nomina')
        ->select("nomina")
        ->where("id_nomina",$id_nomina)
        ->get();
        if(count($nombre_nomina)>0){
            return $this->crearRespuesta(1,$nombre_nomina,200);
        }else{
            return $this->crearRespuesta(2,"Ha ocurrido un error",301);
        }
    }
    public function obtenerLigaEmpresaNomina(Request $res)
    {
        $id_empresa = $res["id_empresa"];
        $id_status = $res["id_status"];
        $str = "=";
        if($id_status == "-1"){
            $str = "!=";
        }
        $recuperarInfoNomina = DB::table('liga_empresa_nomina as len')
        ->select("len.id_empresa_nomina","ncn.nomina","len.activo as id_status","len.activo","ncn.id_nomina")
        ->join("nom_cat_nomina as ncn","ncn.id_nomina","=","len.id_nomina")
        ->where("id_empresa",$id_empresa)
        ->where("len.activo",$str,$id_status)
        ->get();
        if(count($recuperarInfoNomina)){
            foreach($recuperarInfoNomina as $nomina){
                if($nomina->id_status == "1"){
                    $nomina->id_status = "Activo"; 
                }
                if($nomina->id_status == "0"){
                    $nomina->id_status = "Inactivo"; 
                }
            }
            return $this->crearRespuesta(1,$recuperarInfoNomina,200);
        }else{
            return $this->crearRespuesta(2,"No se tiene nominas",200);
        }
    }
    public function insertarLigaNominaEmpresa(Request $res)
    {
        try{
            $id_nomina = $res["id_nomina"];
            $id_empresa = $res["id_empresa"];
            $fecha = $this->getHoraFechaActual();
            $usuario_creacion = $res["usuario_creacion"];
            $validar = DB::table('liga_empresa_nomina')
            ->where("id_empresa",$id_empresa)
            ->where("id_nomina",$id_nomina)
            ->first();
            if($validar){
                DB::update('update liga_empresa_nomina set activo = 1 where id_empresa = ? and id_nomina = ?', [$id_empresa, $id_nomina]);
            }else{
                $id_empresa_nomina = $this->getSigIdBest("liga_empresa_nomina","id_empresa_nomina");
                DB::insert('insert into liga_empresa_nomina (id_empresa_nomina, id_empresa, id_nomina, fecha_creacion, usuario_creacion, activo) values (?,?,?,?,?,?)', [$id_empresa_nomina,$id_empresa,$id_nomina,$fecha,$usuario_creacion,1]);
            }
            
            return $this->crearRespuesta(1,"Se ha agreado el tipo de nómina a la empresa",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function eliminarLigaEmpresaNomina(Request $res)
    {
        try{
            $id_nomina = $res["id_nomina"];
            $id_empresa = $res["id_empresa"];
            DB::update('update liga_empresa_nomina set activo = 0 where id_empresa = ? and id_nomina = ?', [$id_empresa, $id_nomina]);
            return $this->crearRespuesta(1,"Se ha eliminado el tipo de nómina a la empresa",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function activarLigaEmpresaNomina($id_empresa_nomina)
    {
        try{
            DB::update('update liga_empresa_nomina set activo = 1 where id_empresa_nomina = ?', [$id_empresa_nomina]);
            return $this->crearRespuesta(1,"Se ha activado el tipo de nómina a la empresa",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }

    public function aplicarSolicitudesRH(Request $res){
        $id_movimiento = $res["id_movimiento"];
        $usuario = $res["usuario"];
        $fecha = $this->getHoraFechaActual();
        $errores = [];
        $detalle_movimiento = DB::table('rh_detalle_contratacion as rdc')
        ->select("rm.tipo_movimiento","rdc.id_candidato","rdc.id_nomina","rdc.id_puesto","rdc.id_sucursal","rdc.fecha_alta","rdc.sueldo")
        ->join("rh_movimientos as rm","rm.id_movimiento","=","rdc.id_movimiento")
        ->where("rm.id_movimiento",$id_movimiento)
        ->get();
        if(count($detalle_movimiento)>0){
            try{
                foreach($detalle_movimiento as $detalle){
                    $validar = DB::table('nom_empleados')
                    ->where("id_candidato",$detalle->id_candidato)
                    ->get();
                    if(count($validar) == 0){
                        if($detalle->tipo_movimiento == "A"){
                            //Dar de alta puesto
                            $validar_vacantes = $this->agregarOCambioPuesto($detalle->id_puesto,1);
                            if($validar_vacantes["ok"]){
                                DB::insert('insert into nom_empleados (id_candidato, id_estatus, id_nomina, id_puesto, id_sucursal, fecha_ingreso, sueldo_diario, sueldo_complemento, usuario_creacion, fecha_creacion) values (?,?,?,?,?,?,?,?,?,?)', [ $detalle->id_candidato, 1, $detalle->id_nomina, $detalle->id_puesto, $detalle->id_sucursal, $detalle->fecha_alta, $detalle->sueldo, $detalle->sueldo_neto, $usuario, $fecha]);
                                $this->cambiarDeEstatus($detalle->id_candidato,1);
                            }else{
                                array_push($errores,$validar_vacantes["message"]);
                            }
                        }
                        
                    }
                }
                if(count($errores) == 0){
                    DB::update('update rh_movimientos set id_status = 1 where id_movimiento = ?', [$id_movimiento]);
                    return $this->crearRespuesta(1,"La solicitud se ha aplicado con exito",200);
                }
                return $this->crearRespuesta(2,$errores,301);
            }catch(Throwable $e){
                return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
            }
        }
        return $this->crearRespuesta(2,"No se ha encontrado el detalle de la contratación, intentelo de nuevo o contacte al administrador",301);
    }

    public function buscarFolio($folio)
    {
        $datos_cotizacion = Cotizacion::where('folio',$folio)
        ->first();
        if($datos_cotizacion){
            $datos_cotizacion->detalle = DetCotizacion::where('id_cotizacion',$datos_cotizacion->id_cotizacion)
            ->get();
            $respuesta = [
                "busqueda" => $datos_cotizacion,
                "datos_cotizacion" => $this->cotizar($datos_cotizacion->detalle)
            ];
            return $this->crearRespuesta(1,$respuesta,200);
        }
        return $this->crearRespuesta(2,"No se ha encontrado el folio ingresado.",200);
    }

    public function procesarCotizacion(Request $res)
    {
        //Variables globales;
        $cotizacion = new Cotizacion();
        #region [Validaciones]
            if(isset($res["folio"]) && !empty($res["folio"])){
                $cotizacion = Cotizacion::where("folio",$res["folio"])
                ->first();
            }else{
                $cotizacion->folio = $this->obtenerFolio();
            }
            //Obligatorios
            if(!$cotizacion){
                if(!isset($res["cliente"])){
                    return $this->crearRespuesta(2,"El campo 'Cliente' es obligatorio",200);
                }else{
                    if(strlen($res["cliente"] > 100)){
                        return $this->crearRespuesta(2,"El campo 'Cliente' solo aceptap un máximo de 100 caracteres",200);
                    }
                }
                if(!isset($res["fecha"])){
                    return $this->crearRespuesta(2,"El campo 'Fecha' es obligatorio",200);
                }else{
                    $res["fecha"] = date('Y-m-d',strtotime($res["fecha"]));
                }
                if(!isset($res["correo"])){
                    return $this->crearRespuesta(2,"El campo 'Correo' es obligatorio",200);
                }
            }
        #endregion
        try{
            //Se inserta o actualiza la cotizacion
            $cotizacion->cliente = $res["cliente"];
            $cotizacion->fecha = $res["fecha"];
            $cotizacion->id_empresa = $res["id_empresa"];
            $cotizacion->id_status = 1;
            $cotizacion->correo = $res["correo"];
            $cotizacion->save(); //Actualizar o Guardar
            //Reiniciar cotizacion
            if($cotizacion){
                DB::update("UPDATE nom_detcotizaciones SET activo = 0 WHERE id_cotizacion = ?",[$cotizacion->id_cotizacion]);
            }
            //Se inserta o actualiza el detalle de cotizacion
            foreach($res["detalle"] as $detalle){
                $det_cotizacion = new DetCotizacion();
                if($cotizacion){
                    $validar = DetCotizacion::where("id_cotizacion",$cotizacion->id_cotizacion)
                    ->where("identificador",strtoupper($detalle["identificador"]))
                    ->first();
                    if($validar){
                        $det_cotizacion = $validar;
                    }
                }
                $det_cotizacion->id_cotizacion = $cotizacion->id_cotizacion;
                $det_cotizacion->identificador = $detalle["identificador"];
                $det_cotizacion->id_puesto = $detalle["id_puesto"];
                $det_cotizacion->fecha_nacimiento = $detalle["fecha_nacimiento"];
                $det_cotizacion->fecha_ingreso = $detalle["fecha_ingreso"];
                $det_cotizacion->sueldo_mensual = $detalle["sueldo_mensual"];
                $det_cotizacion->notas = $detalle["notas"];
                $det_cotizacion->activo = 1;
                $det_cotizacion->save();

            }
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
        $respuesta = $this->cotizar($res["detalle"]);
        $respuesta["folio"] = $cotizacion->folio;
        return $this->crearRespuesta(1,$respuesta,200);
    }

    #region [Métodos Privados]
        private function obtenerFolio()
        {
            $hoy = getdate();
            return $hoy["year"].$hoy["hours"].$hoy["minutes"].$hoy["seconds"];
        }
        private function cotizar($data)
        {
            # Aqui va su función de cotizar
            $respuesta = [
                "folio" => "",
                "cotizacion" => [
                    "sueldos" => 300.00,
                    "imss_obrero" => 120.00,
                    "ims_patronal" => 30.00,
                    "isn" => 231.00
                ],
                "cotizacion_estrategia" => [
                    "sueldos" => 4210.00,
                    "imss_obrero" => 120.00,
                    "ims_patronal" => 320.00,
                    "isn" => 112.00
                ]
            ];
            return $respuesta;
        }
    #endregion
    
}