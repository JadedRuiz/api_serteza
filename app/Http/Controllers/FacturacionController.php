<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Exports\FacturaExport;
use ZipArchive;
use App\Models\Factura;
use App\Models\DetFactura;
use App\Models\Direccion;
use App\Models\Cataporte;
use App\Models\BobedaXML;
use App\Models\DetalleNomina;
use App\Lib\Timbrado;
use App\Lib\LibSat;
use DOMDocument;
use XSLTProcessor;

class FacturacionController extends Controller
{
    public function obtenerFacturas(Request $res)
    {
        $id_cliente = $res["id_cliente"];
        $filtros = $res["filtros"];
        $id_empresas = [];
        if($id_cliente != "0"){
            $empresas = DB::table('liga_empresa_cliente as lec')
            ->select("id_empresa")
            ->where("id_cliente",$id_cliente)
            ->get();
            foreach($empresas as $empresa){
                array_push($id_empresas,$empresa->id_empresa);
            }
        }else{
           array_push($id_empresas,$res["id_empresa"]);
        }

        if(count($id_empresas)>0){

            //Consultas
            $facturas = DB::table('fact_cattimbrado as fct')
            ->select("id_timbrado","empleado as nombre","rfc","codigo_empleado","uuid",
            DB::raw('DATE_FORMAT(fecha_pago,"%d-%m-%Y") as fecha_pago'),DB::raw('DATE_FORMAT(fecha_timbrado,"%d-%m-%Y") as fecha_timbrado'),'periodo','codigo_nomina','ejercicio')
            ->where("activo",1)
            ->where(function ($query) use ($filtros, $id_empresas){
                if($filtros["id_empresa"] != 0){
                    $query->where("id_empresa",$filtros["id_empresa"]);
                }else{
                    $query->whereIn("id_empresa",$id_empresas);
                }
                if($filtros["id_sucursal"] != 0){
                    $query->where("id_sucursal",$filtros["id_sucursal"]);
                }
                if(strlen($filtros["rfc"]) > 0){
                    $query->where("rfc","like","%".strtoupper($filtros["rfc"])."%");
                }
                if(strlen($filtros["tipo_nomina"]) > 0){
                    $query->where("codigo_nomina",$filtros["tipo_nomina"]);
                }
                if(strlen($filtros["periodo"]) > 0){
                    $query->where("periodo",$filtros["periodo"]);
                }
                if(strlen($filtros["ejercicio"]) > 0){
                    $query->where("ejercicio",$filtros["ejercicio"]);
                }
                if($filtros["fecha_pago_i"] != "" && $filtros["fecha_pago_f"] != ""){
                    $query->whereBetween("fecha_pago",[date('Y-m-d',strtotime($filtros["fecha_pago_i"])),date('Y-m-d',strtotime($filtros["fecha_pago_f"]))]);
                }
                if($filtros["fecha_final"] != "" && $filtros["fecha_inicial"] != ""){
                    $query->whereBetween("fecha_timbrado",[date('Y-m-d',strtotime($filtros["fecha_inicial"])),date('Y-m-d',strtotime($filtros["fecha_final"]))]);
                }
            })
            ->take(1000)
            ->orderBy("id_timbrado","DESC")
            ->get();
            $total = DB::table('fact_cattimbrado as fct')
            ->select("empleado as nombre","rfc","codigo_empleado","fecha_pago","fecha_timbrado")
            ->where("activo",1)
            ->where(function ($query) use ($filtros, $id_empresas){
                if($filtros["id_empresa"] != 0){
                    $query->where("id_empresa",$filtros["id_empresa"]);
                }else{
                    $query->whereIn("id_empresa",$id_empresas);
                }
                if($filtros["id_sucursal"] != 0){
                    $query->where("id_sucursal",$filtros["id_sucursal"]);
                }
                if(strlen($filtros["rfc"]) > 0){
                    $query->where("rfc","like","%".strtoupper($filtros["rfc"])."%");
                }
                if(strlen($filtros["tipo_nomina"]) > 0){
                    $query->where("codigo_nomina",$filtros["tipo_nomina"]);
                }
                if(strlen($filtros["periodo"]) > 0){
                    $query->where("periodo",$filtros["periodo"]);
                }
                if(strlen($filtros["ejercicio"]) > 0){
                    $query->where("ejercicio",$filtros["ejercicio"]);
                }
                if($filtros["fecha_pago_i"] != "" && $filtros["fecha_pago_f"] != ""){
                    $query->whereBetween("fecha_pago",[date('Y-m-d',strtotime($filtros["fecha_pago_i"])),date('Y-m-d',strtotime($filtros["fecha_pago_f"]))]);
                }
                if($filtros["fecha_final"] != "" && $filtros["fecha_inicial"] != ""){
                    $query->whereBetween("fecha_timbrado",[date('Y-m-d',strtotime($filtros["fecha_inicial"])),date('Y-m-d',strtotime($filtros["fecha_final"]))]);
                }
            })
            ->count();
            if(count($facturas)> 0){
                return $this->crearRespuesta(1,[
                    "repuesta" => $facturas,
                    "total" => $total
                ],200);
            }
            return $this->crearRespuesta(2,"No se tienen facturas",200);
        }
        return $this->crearRespuesta(2,"No se tienen empresas configuradas",200);
    }
    public function altaFactura(Request $res)
    {
        //Validaciones

        if(!isset($res["id_empresa"])){
            return $this->crearRespuesta(2,"El parametro 'id_empresa' es obligatorio",301);
        }
        if(isset($res["id_empresa"]) && !is_numeric($res["id_empresa"])){
            return $this->crearRespuesta(2,"El 'id_empresa' debe ser numerico",301);
        }
        if(isset($res["id_sucursal"]) && !is_numeric($res["id_sucursal"])){
            return $this->crearRespuesta(2,"El 'id_sucursal' debe ser numerico",301);
        }
        if(isset($res["rfc"]) && strlen($res["rfc"]) != 13){
            return $this->crearRespuesta(2,"El 'rfc' debe tener 13 digitos",301);
        }
        if(isset($res["empleado"]) && strlen($res["empleado"]) == 0){
            return $this->crearRespuesta(2,"El nombre del empleado no puede ser vacio",301);
        }
        if(isset($res["fecha_pago"]) && strlen($res["fecha_pago"]) == 0){
            return $this->crearRespuesta(2,"El parametro 'fecha_pago' no puede ser vacio",301);
        }
        if(isset($res["fecha_timbrado"]) && strlen($res["fecha_timbrado"]) == 0){
            return $this->crearRespuesta(2,"El parametro 'fecha_timbrado' no puede ser vacio",301);
        }
        $validar_rfc = DB::table('fact_cattimbrado')
        ->select("rfc")
        ->where("rfc",$res["rfc"])
        ->where("codigo_nomina",$res["tipo_nomina"])
        ->where("periodo",$res["id_periodo"])
        ->get();
        if(count($validar_rfc)>0){
            return $this->crearRespuesta(2,"Este 'RFC' ya ha sido dado de alta en este periodo con el tipo de nomina ".$res["tipo_nomina"],301);
        }
        $xml = "";
        if(isset($res["xml"])){
            $respuesta = "";
            if($res["xml"] == ""){
                return $this->crearRespuesta(2,"El paremetro 'xml' no puede ser vacio",301);
            }
            $res["xml"] = base64_decode($res["xml"])."";
            $res["xml"] = trim($res["xml"], " \n");
            $res["xml"] = trim($res["xml"], " \t");
            $res["xml"] = str_replace('\"','"',$res["xml"]);
            if(isset($res["correo"]) && strlen($res["correo"]) != 0){
                $reporte = new FacturaExport();
                $respuesta =  $reporte->generarReporteFactura([
                    "id_empresa" => $res["id_empresa"],
                    "xml" => $res["xml"],
                    "tipo" => true
                ]);
                //Enviar correo
                $recuperar_logo = DB::table('gen_cat_empresa as gce')
                ->select("gcf.nombre as logo")
                ->leftJoin("gen_cat_fotografia as gcf","gcf.id_fotografia","=","gce.id_fotografia")
                ->where("gce.id_empresa",$res["id_empresa"])
                ->first();
                if($recuperar_logo){
                    $path = storage_path('empresa')."/".$recuperar_logo->logo;
                    $extension_logo = pathinfo($path, PATHINFO_EXTENSION);
                    $logo_empresa = base64_encode(file_get_contents($path));
                }else{
                    $path = storage_path('empresa')."/empresa_default.png";
                    $extension_logo = "png";
                    $logo_empresa = base64_encode(file_get_contents($path));
                }
                $this->enviarCorreo([
                    "rfc" => getenv("RFC_CORREO"),
                    "tipo" => 1,
                    "dirigidos" => [
                        [
                            "correo" => $res["correo"],
                            "nombre" => $res["empleado"]
                        ],
                    ],
                    "asunto" => "RECIBO DE NÓMINA",
                    "mensaje" => "Le hemos enviado sus archivos correspondientes",
                    "adjuntos" => [
                        [
                            "extension" => "pdf",
                            "nombre" => "RECIBO_NOMINA",
                            "data" => $respuesta["pdf"]
                        ],
                        [
                            "extension" => "xml",
                            "nombre" => "FORMTAO_XML",
                            "data" => base64_encode($res["xml"])
                        ]
                        ],
                    "logo" => $logo_empresa,
                    "extension_logo" => $extension_logo
                ]);
            }
            $xml = base64_encode($res["xml"]);
        }
        try{
            DB::insert('insert into fact_cattimbrado (id_empresa, id_sucursal, uuid, periodo, ejercicio, codigo_empleado, rfc, empleado, codigo_nomina, fecha_pago, fecha_timbrado, xml, activo) values (?,?,?,?,?,?,?,?,?,?,?,?,?)', [$res["id_empresa"],$res["id_sucursal"],$res["uuid"], $res["periodo"], $res["ejercicio"], $res["codigo_empleado"],$res["rfc"],$res["empleado"],$res["codigo_nomina"],$res["fecha_pago"],$res["fecha_timbrado"],$xml,1]);
            return $this->crearRespuesta(1,"El timbre se ha registrado con éxito",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function opcionesFactura(Request $res){
        $id_timbrado = $res["id_timbrado"];
        $tipo = $res["tipo"];
        $xml = DB::table('fact_cattimbrado as fc')
        ->select("uuid","xml","fc.id_empresa","empleado","gcf.nombre as logo","gce.empresa")
        ->leftJoin("gen_cat_empresa as gce","gce.id_empresa","=","fc.id_empresa")
        ->leftJoin("gen_cat_fotografia as gcf","gcf.id_fotografia","=","gce.id_fotografia")
        ->where("id_timbrado",$id_timbrado)
        ->first();
        if($xml){
            if($tipo == 1){     //Descarga XML
                $xml->xml = base64_encode($xml->xml);
                return $this->crearRespuesta(1,$xml,200);
            }
            if($tipo == 2){
                $reporte = new FacturaExport();
                $respuesta =  $reporte->generarReporteFactura([
                    "id_empresa" => $xml->id_empresa,
                    "xml" => $xml->xml,
                    "tipo" => true
                ]);
                $xml->xml = $respuesta["pdf"];
                return $this->crearRespuesta(1,$xml,200);
            }
            if($tipo == 3){
                $reporte = new FacturaExport();
                $respuesta =  $reporte->generarReporteFactura([
                    "id_empresa" => $xml->id_empresa,
                    "xml" => $xml->xml,
                    "tipo" => true
                ]);
                if($xml->logo != ""){
                    $path = storage_path('empresa')."/".$xml->logo;
                    $extension_logo = pathinfo($path, PATHINFO_EXTENSION);
                    $logo_empresa = base64_encode(file_get_contents($path));
                }else{
                    $path = storage_path('empresa')."/empresa_default.png";
                    $extension_logo = "png";
                    $logo_empresa = base64_encode(file_get_contents($path));
                }
                $this->enviarCorreo([
                    "rfc" => getenv("RFC_CORREO"),
                    "titulo" => $xml->empresa,
                    "tipo" => 1,
                    "dirigidos" => [
                        [
                            "correo" => $res["correo"],
                            "nombre" => $xml->empleado
                        ],
                    ],
                    "asunto" => "RECIBO DE NÓMINA",
                    "mensaje" => "Le hemos enviado sus archivos correspondientes",
                    "adjuntos" => [
                        [
                            "extension" => "pdf",
                            "nombre" => "RECIBO_NOMINA",
                            "data" => $respuesta["pdf"]
                        ],
                        [
                            "extension" => "xml",
                            "nombre" => "FORMTAO_XML",
                            "data" => base64_encode($xml->xml)
                        ]
                    ],
                    "logo" => $logo_empresa,
                    "extension_logo" => $extension_logo
                ]);
                return $this->crearRespuesta(1,"Correo enviado con éxito",200);
            }
        }
        return $this->crearRespuesta(1,"No se ha encontrado registro con el id_timbrado",200);
    }
    public function generarExcel(Request $res)
    {
        $id_cliente = $res["id_cliente"];
        $filtros = $res["filtros"];
        $empresas = DB::table('liga_empresa_cliente as lec')
        ->select("id_empresa")
        ->where("id_cliente",$id_cliente)
        ->get();
        if(count($empresas)>0){
            $id_empresas = [];
            foreach($empresas as $empresa){
                array_push($id_empresas,$empresa->id_empresa);
            }
            //Consultas
            $facturas = DB::table('fact_cattimbrado as fct')
            ->select("id_timbrado","empleado as nombre","rfc","codigo_empleado","uuid",DB::raw('DATE_FORMAT(fecha_pago,"%d-%m-%Y") as fecha_pago'),DB::raw('DATE_FORMAT(fecha_timbrado,"%d-%m-%Y") as fecha_timbrado'))
            ->where("activo",1)
            ->where(function ($query) use ($filtros, $id_empresas){
                if($filtros["id_empresa"] != 0){
                    $query->where("id_empresa",$filtros["id_empresa"]);
                }else{
                    $query->whereIn("id_empresa",$id_empresas);
                }
                if($filtros["id_sucursal"] != 0){
                    $query->where("id_sucursal",$filtros["id_sucursal"]);
                }
                if(strlen($filtros["rfc"]) > 0){
                    $query->where("rfc","like","%".strtoupper($filtros["rfc"])."%");
                }
                if(strlen($filtros["tipo_nomina"]) > 0){
                    $query->where("codigo_nomina",$filtros["tipo_nomina"]);
                }
                if(strlen($filtros["periodo"]) > 0){
                    $query->where("periodo",$filtros["periodo"]);
                }
                if(strlen($filtros["ejercicio"]) > 0){
                    $query->where("ejercicio",$filtros["ejercicio"]);
                }
                if($filtros["fecha_pago_i"] != "" && $filtros["fecha_pago_f"] != ""){
                    $query->whereBetween("fecha_pago",[date('Y-m-d',strtotime($filtros["fecha_pago_i"])),date('Y-m-d',strtotime($filtros["fecha_pago_f"]))]);
                }
                if($filtros["fecha_final"] != "" && $filtros["fecha_inicial"] != ""){
                    $query->whereBetween("fecha_timbrado",[date('Y-m-d',strtotime($filtros["fecha_inicial"])),date('Y-m-d',strtotime($filtros["fecha_final"]))]);
                }
            })
            ->take(1000)
            ->orderBy("id_timbrado","DESC")
            ->get();
            if(count($facturas)>0){
                $fatura_exp = new FacturaExport();
                try{
                    return $this->crearRespuesta(1,$fatura_exp->generarExcelReporte($facturas),200);
                }catch(Throwable $e){
                    return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
                }
            }
            return $this->crearRespuesta(2,"No se han encontrado facturas",200);
        }
    }
    public function descargaMasiva(Request $res)
    {
        $id_cliente = $res["id_cliente"];
        $filtros = $res["filtros"];
        $empresas = DB::table('liga_empresa_cliente as lec')
        ->select("id_empresa")
        ->where("id_cliente",$id_cliente)
        ->get();
        if(count($empresas)>0){
            $id_empresas = [];
            foreach($empresas as $empresa){
                array_push($id_empresas,$empresa->id_empresa);
            }
            //Consultas
            $facturas = DB::table('fact_cattimbrado as fct')
            ->select("empleado as pdf", "xml","uuid","id_empresa")
            ->where("activo",1)
            ->where(function ($query) use ($filtros, $id_empresas){
                if($filtros["id_empresa"] != 0){
                    $query->where("id_empresa",$filtros["id_empresa"]);
                }else{
                    $query->whereIn("id_empresa",$id_empresas);
                }
                if($filtros["id_sucursal"] != 0){
                    $query->where("id_sucursal",$filtros["id_sucursal"]);
                }
                if(strlen($filtros["rfc"]) > 0){
                    $query->where("rfc","like","%".strtoupper($filtros["rfc"])."%");
                }
                if(strlen($filtros["tipo_nomina"]) > 0){
                    $query->where("codigo_nomina",$filtros["tipo_nomina"]);
                }
                if(strlen($filtros["periodo"]) > 0){
                    $query->where("periodo",$filtros["periodo"]);
                }
                if(strlen($filtros["ejercicio"]) > 0){
                    $query->where("ejercicio",$filtros["ejercicio"]);
                }
                if($filtros["fecha_pago_i"] != "" && $filtros["fecha_pago_f"] != ""){
                    $query->whereBetween("fecha_pago",[date('Y-m-d',strtotime($filtros["fecha_pago_i"])),date('Y-m-d',strtotime($filtros["fecha_pago_f"]))]);
                }
                if($filtros["fecha_final"] != "" && $filtros["fecha_inicial"] != ""){
                    $query->whereBetween("fecha_timbrado",[date('Y-m-d',strtotime($filtros["fecha_inicial"])),date('Y-m-d',strtotime($filtros["fecha_final"]))]);
                }
            })
            ->take(1000)
            ->orderBy("id_timbrado","DESC")
            ->get();
            if(count($facturas)>0){
                try{
                    foreach($facturas as $factura){
                        $reporte = new FacturaExport();
                        $respuesta =  $reporte->generarReporteFactura([
                            "id_empresa" => $factura->id_empresa,
                            "xml" => $factura->xml,
                            "tipo" => true
                        ]);
                        $factura->pdf = $respuesta["pdf"];
                        $factura->xml = base64_encode($factura->xml);
                    }
                    return $this->crearRespuesta(1,$facturas,200);
                }catch(Throwable $e){
                    return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
                }
            }
        }
    }
    public function facObtenerOperadores($id_empresa)
    {
        $operadores = DB::table('fac_catoperador')
        ->where("id_empresa",$id_empresa)
        ->get();
        if(count($operadores)>0){
            return $this->crearRespuesta(1,$operadores,200);
        }
        return $this->crearRespuesta(2,"No se han encontrado los operadores",200);
    }
    public function facAltaOperador(Request $res)
    {
        //validaciones
        if(!isset($res["id_empresa"])){
            return $this->crearRespuesta(2,"Es necesario pasar el id_empresa",200);
        }
        if(!isset($res["rfc"])){
            return $this->crearRespuesta(2,"El campo 'RFC' del operador es obligatorio",200);
        }
        if(!isset($res["nom_operador"])){
            return $this->crearRespuesta(2,"El campo 'Nombre' del operador es obligatorio",200);
        }
        if(!isset($res["num_licencia"])){
            return $this->crearRespuesta(2,"El campo 'Número de licencia' del operador es obligatorio",200);
        }
        $validar_rfc = DB::table('sat_CodigoPostal as scp')
        ->where("c_CodigoPostal",$res["direccion"]["codigo_postal"])
        ->first();
        if(!$validar_rfc){
            return $this->crearRespuesta(2,"El Codigo Postal ingresado no se ha encontrado en el catálogo del sat, intente con otro.",200);
        }
        try{
            $fecha = $this->getHoraFechaActual();
            $direccion = new Direccion;
            $direccion->calle = strtoupper($res["direccion"]["calle"]);
            $direccion->numero_interior = strtoupper($res["direccion"]["numero_interior"]);
            $direccion->numero_exterior = strtoupper($res["direccion"]["numero_exterior"]);
            $direccion->cruzamiento_uno = strtoupper($res["direccion"]["cruzamiento_uno"]);
            $direccion->codigo_postal = $res["direccion"]["codigo_postal"];
            $direccion->colonia = strtoupper($res["direccion"]["colonia"]);
            $direccion->localidad = strtoupper($res["direccion"]["localidad"]);
            $direccion->municipio = strtoupper($res["direccion"]["municipio"]);
            $direccion->estado = $res["direccion"]["estado"];
            $direccion->descripcion = strtoupper($res["direccion"]["descripcion"]);
            $direccion->fecha_creacion = $fecha;
            $direccion->activo = 1;
            $direccion->save();
            $id_direccion = $direccion->id_direccion;
            $nombre = strtoupper($res["nom_operador"]);
            $rfc = strtoupper($res["rfc"]);
            DB::insert('insert into fac_catoperador (id_empresa, id_direccion, rfc, nombre_operador, num_licencia) values (?,?,?,?,?)', [$res["id_empresa"],$id_direccion,$rfc,$nombre,$res["num_licencia"]]);
            return $this->crearRespuesta(1,"Se ha registrado el operador a la empresa",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function facObtenerTransporte($id_empresa,$tipo)
    {
        if($tipo == 1){
            $tipo = "T";
        }else{
            $tipo = "R";
        }
        $vehiculos = DB::table('fac_catvehiculo')
        ->where("tipo_vehiculo",$tipo)
        ->get();
        if(count($vehiculos)>0){
            return $this->crearRespuesta(1,$vehiculos,200);
        }
        return $this->crearRespuesta(2,"No se tiene remolques o vehiculos",200);
    }
    public function facAltaVehiculo(Request $res)
    {
        //Validaciones
        $tipo_vehiculo = "T";
        if(!isset($res["id_empresa"])){
            return $this->crearRespuesta(2,"Es necesario especificar el id_empresa",200);
        }
        if(!isset($res["tipo_vehiculo"])){
            return $this->crearRespuesta(2,"Es necesario especificar el tipo de vehiculo",200);
        }
        if(!isset($res["num_economico"])){
            return $this->crearRespuesta(2,"El campo 'PLACA' es obligario",200);
        }
        if(!isset($res["placa"])){
            return $this->crearRespuesta(2,"El campo 'PLACA' es obligario",200);
        }
        if($res["tipo_vehiculo"] == 2){
            $tipo_vehiculo = "R";
            if(!isset($res["tipo_permiso"])){
                return $this->crearRespuesta(2,"El campo 'Subtipo Remolque' es obligario",200);
            }
        }
        if($res["tipo_vehiculo"] == 1){
            if(!isset($res["anio"])){
                return $this->crearRespuesta(2,"El campo 'Año' es obligario",200);
            }
            if(!isset($res["configuracion"])){
                return $this->crearRespuesta(2,"El campo 'Configuración' es obligario",200);
            }
            if(!isset($res["num_permiso"])){
                return $this->crearRespuesta(2,"El campo 'Número de permiso' es obligario",200);
            }
            if(!isset($res["tipo_permiso"])){
                return $this->crearRespuesta(2,"El campo 'Tipo permiso' es obligario",200);
            }
            if(!isset($res["aseguradora_resp_civil"])){
                return $this->crearRespuesta(2,"El campo 'Nombre aseguradora' es obligario",200);
            }
            if(!isset($res["poliza_resp_civil"])){
                return $this->crearRespuesta(2,"El campo 'Poliza' es obligario",200);
            }
        }
        try{
            DB::insert('insert into fac_catvehiculo (id_empresa, tipo_vehiculo, num_economico, placa, anio, tipo_permiso, num_permiso, configuracion, aseguradora_resp_civil, poliza_resp_civil, asegurador_ambiente, poliza_ambiente, asegurador_carga, poliza_carga, prima_seguro) values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [$res["id_empresa"],$tipo_vehiculo,$res["num_economico"],$res["placa"],$res["anio"],$res["tipo_permiso"],$res["num_permiso"],$res["configuracion"],$res["aseguradora_resp_civil"],$res["poliza_resp_civil"],$res["asegurador_ambiente"],$res["poliza_ambiente"],$res["asegurador_carga"],$res["poliza_carga"],$res["prima_seguro"]]);
            return $this->crearRespuesta(1,"Se ha registrado el vehiculo",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function facObtenerPersona($id_empresa)
    {
        $personas = DB::table('fac_catpersona')
        ->where("id_empresa",$id_empresa)
        ->get();
        if(count($personas)>0){
            return $this->crearRespuesta(1,$personas,200);
        }
        return $this->crearRespuesta(2,"No se encontro",200);

    }
    public function facAltaPersona(Request $res)
    {
        //validaciones
        if(!isset($res["id_empresa"])){
            return $this->crearRespuesta(2,"Es necesario pasar el id_empresa",200);
        }
        if(!isset($res["rfc"])){
            return $this->crearRespuesta(2,"El campo 'RFC' es obligatorio",200);
        }
        if(!isset($res["nom_operador"])){
            return $this->crearRespuesta(2,"El campo 'Nombre' es obligatorio",200);
        }
        if(!isset($res["tipo_persona"])){
            return $this->crearRespuesta(2,"El campo 'Tipo' es obligatorio",200);
        }
        $validar_rfc = DB::table('sat_CodigoPostal as scp')
        ->where("c_CodigoPostal",$res["direccion"]["codigo_postal"])
        ->first();
        if(!$validar_rfc){
            return $this->crearRespuesta(2,"El Codigo Postal ingresado no se ha encontrado en el catálogo del sat, intente con otro.",200);
        }
        try{
            $fecha = $this->getHoraFechaActual();
            $direccion = new Direccion;
            $direccion->calle = strtoupper($res["direccion"]["calle"]);
            $direccion->numero_interior = strtoupper($res["direccion"]["numero_interior"]);
            $direccion->numero_exterior = strtoupper($res["direccion"]["numero_exterior"]);
            $direccion->cruzamiento_uno = strtoupper($res["direccion"]["cruzamiento_uno"]);
            $direccion->codigo_postal = $res["direccion"]["codigo_postal"];
            $direccion->colonia = strtoupper($res["direccion"]["colonia"]);
            $direccion->localidad = strtoupper($res["direccion"]["localidad"]);
            $direccion->municipio = strtoupper($res["direccion"]["municipio"]);
            $direccion->estado = $res["direccion"]["estado"];
            $direccion->descripcion = strtoupper($res["direccion"]["descripcion"]);
            $direccion->fecha_creacion = $fecha;
            $direccion->activo = 1;
            $direccion->save();
            $id_direccion = $direccion->id_direccion;
            $nombre = strtoupper($res["nom_operador"]);
            $rfc = strtoupper($res["rfc"]);
            DB::insert('insert into fac_catpersona (id_empresa, id_direccion, rfc, nombre, tipo_persona) values (?,?,?,?,?)', [$res["id_empresa"],$id_direccion,$rfc,$nombre,$res["tipo_persona"]]);
            return $this->crearRespuesta(1,"Se ha registrado correctamente",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function facObtenerUbicacion(Request $res)
    {
        $id_cliente = $res["id_cliente"];
        $busqueda = $res["busqueda"];
        $ubicaciones = DB::table('fac_catubicacion')
        ->select("nombre","lugar","id_ubicacion","folio_sat","tipo")
        ->where(function($query) use ($busqueda) {
            if($busqueda != ""){
                $busqueda = "%".$busqueda."%";
                $query->where("nombre","like",$busqueda)
                ->orWhere("lugar","like",$busqueda);
            }
        })
        ->get();
        if(count($ubicaciones)>0){
            return $this->crearRespuesta(1,$ubicaciones,200);
        }
        return $this->crearRespuesta(2,"No se encontraron ubicaciones",200);
    }
    public function facAltaUbicacion(Request $res)
    {
        //Validaciones
        if(!isset($res["id_cliente"])){
            return $this->crearRespuesta(2,"El 'id_cliente' es obligatorio",200);
        }
        if(!isset($res["lugar"])){
            return $this->crearRespuesta(2,"El 'lugar' es obligatorio",200);
        }
        if(!isset($res["tipo"])){
            return $this->crearRespuesta(2,"El 'tipo' es obligatorio",200);
        }
        if(!isset($res["rfc"])){
            return $this->crearRespuesta(2,"El 'rfc' es obligatorio",200);
        }
        try{
            $fecha_hoy = $this->getHoraFechaActual();
            $direccion = new Direccion;
            $direccion->calle = strtoupper($res["direccion"]["calle"]);
            $direccion->numero_interior = strtoupper($res["direccion"]["numero_interior"]);
            $direccion->numero_exterior = strtoupper($res["direccion"]["numero_exterior"]);
            $direccion->cruzamiento_uno = strtoupper($res["direccion"]["cruzamiento_uno"]);
            $direccion->codigo_postal = $res["direccion"]["codigo_postal"];
            $direccion->colonia = strtoupper($res["direccion"]["colonia"]);
            $direccion->localidad = strtoupper($res["direccion"]["localidad"]);
            $direccion->municipio = strtoupper($res["direccion"]["municipio"]);
            $direccion->estado = $res["direccion"]["estado"];
            $direccion->descripcion = strtoupper($res["direccion"]["descripcion"]);
            $direccion->fecha_creacion = $fecha_hoy;
            $direccion->activo = 1;
            $direccion->save();
            $id_direccion = $direccion->id_direccion;
            $fecha = date('Y-m-d',strtotime($res["Fecha"]));
            DB::insert('insert into fac_catubicacion (id_cliente, id_direccion, tipo, lugar, folio_sat, rfc, nombre, nombre_estacion, activo) values (?,?,?,?,?,?,?,?,?)', [$res["id_cliente"],$id_direccion,$res["tipo"],$res["lugar"],$res["folio_sat"],$res["rfc"],$res["nombre"],$res["nombre_estacion"],1]);
            return $this->crearRespuesta(1,"La ubicacion se ha agregado con éxito",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function getImportMercancias()
    {
        $fatura_exp = new FacturaExport();
        try{
            return $this->crearRespuesta(1,$fatura_exp->generarMercanciaImport(),200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function facObtenerFacturas(Request $res)
    {
        $id_empresa = $res["id_empresa"];
        $id_serie = $res["id_serie"];

        try{
            $facturas = DB::table("fac_factura as ff")
            ->select("ff.id_factura","ff.folio","fc.serie",DB::raw("date_format(ff.fecha_creacion, '%d-%m-%Y') as fecha"),"ff.total","ff.observaciones")
            ->leftJoin("fac_catseries as fc","fc.id_serie","=","ff.id_serie")
            ->where("ff.id_empresa",$id_empresa)
            ->where("fc.id_serie",$id_serie)
            ->orderBy("ff.fecha_creacion","DESC")
            ->get();
            if(count($facturas)>0){
                return $this->crearRespuesta(1,$facturas,200);
            }
            return $this->crearRespuesta(2,"Ha ocurrido un error : No se han encontrado facturas con la empresa y serie seleccionada",200);
        }catch(Throweable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),200);
        }
    }
    public function facAltaFactura(Request $res)
    {
        //Validaciones
        $timbrado = new Timbrado();
        $resultado =  $timbrado->timbrar($res);
        if($resultado["ok"]){
            $result = $this->guardarFactura($res,$resultado["data"]);
            if($result["ok"]){
                $reporte = new FacturaExport();
                $result_report = $reporte->generarFactura($result["data"]);
                if($result_report["ok"]){
                    $xml = DB::table("fac_factura")
                    ->select("xml")
                    ->where("id_factura",$result["data"])
                    ->first();
                    $xml_b64 = "";
                    if($xml){
                        $xml_b64 = base64_encode($xml->xml);
                    }
                    return $this->crearRespuesta(1,[
                        "docB64" => $result_report["data"],
                        "xml" => $xml_b64,
                        "id_factura" => $result["data"]
                        ], 200);
                }
                return $this->crearRespuesta(1,"1", 200);
            }
            return $this->crearRespuesta(2,$result["message"], 200);
        }
        return $this->crearRespuesta(2,$resultado["message"], 200);
    }
    public function guardarFactura($res,$xml)
    {
        try{
            $fecha = $this->getHoraFechaActual();
            $usuario_creacion = $res["usuario_creacion"];
            $factura = new Factura();
            $factura->id_empresa = $res["id_empresa"];
            $factura->id_catclientes = $res["id_cliente"];
            $factura->id_serie = $res["id_serie"];
            $factura->id_status = "10";
            $factura->xml = $xml;
            $factura->folio = $res["folio"];
            $factura->id_formapago = $res["id_formapago"];
            $factura->id_metodopago = $res["id_metodopago"];
            $factura->numero_cuenta = $res["numero_cuenta"];
            $factura->id_tipomoneda = $res["id_tipomoneda"];
            $factura->id_usocfdi = $res["id_usocfdi"];
            $factura->id_tipocomprobante = $res["tipo_comprobante"];
            $factura->condicion_pago = $res["condiciones"];
            $factura->tipo_cambio = $res["tipo_cambio"];
            $factura->observaciones = strtoupper($res["observaciones"]);
            $factura->usa_ine = $res["usa_ine"];
            $factura->usa_cataporte = $res["usa_cataporte"];
            $factura->subtotal = $res["subtotal"];
            $factura->descuento = $res["descuento_t"];
            $factura->iva = $res["iva_t"];
            $factura->ieps = $res["ieps_t"];
            $factura->otros = $res["otros_t"];
            $factura->total = $res["total"];
            $factura->fecha_creacion = $fecha;
            $factura->usuario_creacion = $usuario_creacion;
            $factura->activo = 1;
            $factura->save();
            $id_factura = $factura->id_factura;
            $conceptos = $res["conceptos"];
            foreach($conceptos as $concepto){
                $detfactura = new DetFactura();
                $detfactura->id_factura = $id_factura;
                $detfactura->id_concepto = $concepto["id_concepto"];
                $detfactura->descripcion = $concepto["descripcion"];
                $detfactura->cantidad = $concepto["cantidad"];
                $detfactura->importe = $concepto["precio"];
                $detfactura->descuento = $concepto["descuento"];
                $detfactura->iva = $concepto["iva"];
                $detfactura->ieps = $concepto["ieps"];
                $detfactura->otros_imp = $concepto["otros"];
                $detfactura->subtotal = $concepto["neto"];
                $detfactura->total = $concepto["importe"];
                $detfactura->fecha_creacion = $fecha;
                $detfactura->usuario_creacion = $usuario_creacion;
                $detfactura->activo = 1;
                $detfactura->save();
            }
            if($res["usa_cataporte"]){
                $cataporte = new Cataporte();
                $cataporte->id_factura = $id_factura;
                $cataporte->id_operador = $res["cataporte"]["transporte"]["id_operador"];
                $cataporte->id_vehiculo = $res["cataporte"]["transporte"]["id_vehiculo"];
                $cataporte->id_remolque = $res["cataporte"]["transporte"]["id_remolque"];
                $cataporte->id_propietario = $res["cataporte"]["transporte"]["id_propietario"];
                $cataporte->save();
                $id_cataporte = $cataporte->id_cataporte;
                foreach($res["cataporte"]["ubicaciones"] as $ubicacion){
                    DB::insert('insert into fac_ubicaciones (id_cataporte, id_catubicacion, distancia_recorrida, fecha) values (?,?,?,?)', [
                        $id_cataporte,
                        $ubicacion["id_lugar"],
                        $ubicacion["distancia_recorrida"],
                        date('Y-m-d h:i:s',strtotime($ubicacion["fecha_hora"]))
                    ]);
                }
                foreach($res["cataporte"]["mercancias"] as $mercancia ){
                    DB::insert('insert into fac_mercancias (id_cataporte, bienes_trasportados, clave_producto, descripcion, cantidad, id_ClaveUnidad, unidad, meterial_peligroso, dimensiones, id_TipoEmbajale, desc_embalaje, peso, valor, id_Moneda, fraccion_arancelaria, uuid_comercio) values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [
                        $id_cataporte,
                        $mercancia["bienes_trans"],
                        $mercancia["clave_prod"],
                        $mercancia["descripcion"],
                        $mercancia["cantidad"],
                        $mercancia["clave_uni"],
                        $mercancia["unidad"],
                        $mercancia["meterial_pel"],
                        $mercancia["dimensiones"],
                        $mercancia["embalaje"],
                        $mercancia["desc_embalaje"],
                        $mercancia["peso"],
                        $mercancia["valor"],
                        $mercancia["moneda"],
                        $mercancia["fraccion_aran"],
                        $mercancia["uuid_ext"]
                    ]);
                }
            }
            return ["ok" => true, "data" => $id_factura];
        }catch(Throwable $e){
            return ["ok" => false, "message" => $e->getMessage()];
        }
    }
    public function generarFactura($id_factura,$tipo,$tipo_envio)
    {
        if($tipo == 1){     //PDF
            $reporte = new FacturaExport();
            switch($tipo_envio){
                case 1 :    //BASE64
                    return $reporte->generarFactura($id_factura);
                    break;
                case 2 :    //Descarga
                    //$path = 'temp_file_pdf.pdf';
                    $contents = base64_decode($reporte->generarFactura($id_factura)["data"]);
                    //file_put_contents($path, $contents);
                    return response($contents)
                	->header('Content-Type','application/pdf')
                	->header('Pragma','public')
                	->header('Content-Disposition','inline; filename="qrcodeimg.pdf"')
                	->header('Cache-Control','max-age=60, must-revalidate');
                    break;
                default :
                    break;
            }
        }
        if($tipo == 2){     //XML
            $xml = base64_encode(DB::table("fac_factura")->select("xml")->where("id_factura",$id_factura)->first()->xml);
            switch($tipo_envio){
                case 1 :    //BASE64
                    return $this->crearRespuesta(1,$xml,"200");
                    break;
                case 2 :    //Descarga
                    $path = 'temp_file_xml.xml';
                    $contents = base64_decode($xml);
                    file_put_contents($path, $contents);
                    $headers = array(
                      'Content-Type: application/octet-stream',
                      'Content-Disposition: attachment; filename=factura.xml'
                    );
                    return response()->download($path, 'factura.xml',$headers)->deleteFileAfterSend(true);
                    break;
                default :
                    break;
            }
        }
        if($tipo == 3){     //Ambos

        }
        if($tipo == 4){     //Información

        }
    }
    public function generaFacturaPreview(Request $res)
    {
        $reporte = new FacturaExport();
        return $reporte->generaFacturaPreview($res);
    }

    public function descargaMasivaSAT(Request $res)
    {
        $lib = new LibSat();
        //Arreglo para el metodo de consulta
        // $datos = [
        //     "id_empresa" => $res["id_empresa"],
        //     "password" => 'exagvd37',
        //     "fecha_inicial" => '2022-04-05 00:00:00',
        //     "fecha_final" => '2022-04-29 23:59:59',
        //     "rfc" => 'TEAA860214SK8',
        //     "emitidos" => true,
        //     "recibidos" => false
        // ];
        // $servicio = $lib->crearSolcitud($datos);
        //Areglo para la validar
        // $datos = [
        //     "id_empresa" => $res["id_empresa"],
        //     "password" => 'exagvd37',
        //     "id_solicitud" => 'd06470be-2035-4c24-9882-60091e2cdd4e',
        // ];
        // $servicio = $lib->verificar($datos);
        //Arreglo para el metodo descarga
        $datos = [
            "id_empresa" => $res["id_empresa"],
            "password" => 'exagvd37',
            "archivos" => [
                'D06470BE-2035-4C24-9882-60091E2CDD4E_01'
            ],
        ];
        $servicio = $lib->descargar($datos);
        return $servicio;
    }
    public function crearSolicitudSat(Request $request){
        // try {
            $lib = new LibSat();
            $id_empresa = $request["id_empresa"];
            $fecha_inicio = $request["fecha_inicio"];
            $fecha_final = $request["fecha_final"];
            $rfc = $request["rfc"];
            $recibidos = $request["recibidos"];
            $emitidos = $request["emitidos"];   
            $id_usuario = $request["id_usuario"];    
 
            $id_estatus = 1; // en espera

            //Arreglo para el metodo de consulta
            $pass = DB::table("gen_cat_empresa")
                    ->select("firma_contra")
                    ->where("id_empresa", $id_empresa)
                    ->first();
            $datos = [
                "id_empresa" => $id_empresa,
                "password" => $pass->firma_contra,
                "fecha_inicial" => $fecha_inicio." 00:00:00",
                "fecha_final" => $fecha_final." 23:59:59",
                "rfc" => $rfc,
                "emitidos" => $emitidos,
                "recibidos" => $recibidos
            ];
            $servicio = $lib->crearSolcitud($datos);
            // print_r($servicio);
            $clave_solicitud = $servicio->getRequestId();
            print_r($servicio->getStatus()->getCode());
 
            if($servicio->getStatus()->isAccepted()){
                DB::insert('insert into con_solicitudes_sat 
                         (id_empresa, id_estatus, fecha_inicial, fecha_final, rfc, 
                         recibidos, emitidos, clave_solicitud, activo, fecha_creacion, 
                         fecha_modificacion, usuario_creacion, usuario_modificacion, nombre_archivo_sat) values 
                         (?,?,?,?,?,?,?,?,?,?,?,?,?,?)', 
                         [$id_empresa, $id_estatus, $fecha_inicio, $fecha_final, $rfc, $recibidos,
                         $emitidos, $clave_solicitud, 1, $this->getHoraFechaActual(), 
                         $this->getHoraFechaActual(), $id_usuario, $id_usuario, ""]);
            }
            // echo $servicio->data->estatus->code;
            return $servicio;

            
            

        // } catch (\Throwable $th) {
        //     return $this->crearRespuesta(0,"Ha ocurrido un error al momento de crear la solicitud ".$th->getMessage(),"300");
        // }
        
    }
    public function verificarEstatusSat(Request $request){
        $lib = new LibSat();
        $mi_estatus = 0;
        $mi_archivo = "";
        $id_empresa = $request["id_empresa"];
        $id_solicitud_sat = $request["id_solicitud_sat"];
        $clave_solicittud_sat = $request["clave_solicitud_sat"];
        $id_usuario = $request["id_usuario"];
        $bandera = 0;
        $pass = DB::table("gen_cat_empresa")
        ->select("firma_contra")
        ->where("id_empresa", $id_empresa)
        ->first();
        $datos = [
                "id_empresa" => $id_empresa,
                "password" => $pass->firma_contra,
                "id_solicitud" => $clave_solicittud_sat,
        ];
        $servicio = $lib->verificar($datos);

        $statusRequest = $servicio->getStatusRequest();

        if ($statusRequest->isExpired() || $statusRequest->isFailure() || $statusRequest->isRejected()) {
            //Cancelado
            $mi_estatus = 7;
        }
        if ($statusRequest->isInProgress() || $statusRequest->isAccepted()) {
            $mi_estatus = 1;
        }
        if ($statusRequest->isFinished()) {
            //TODO: Actualizar el estatus y el campo de archivo
            $bandera = 1;
            $mi_estatus = 14;
            $archivo = $servicio->getPackagesIds();
            $mi_archivo = $archivo[0];
            
        }
        DB::update('update con_solicitudes_sat 
            set usuario_modificacion = ?, fecha_modificacion = ?,
            nombre_archivo_sat = ?, id_estatus = ?
            where id_solicitud_sat = ?', 
            [$id_usuario, $this->getHoraFechaActual(), $mi_archivo, 
                $mi_estatus, $id_solicitud_sat]);

        return $this->crearRespuesta(1,"Se ha aceptado la solicitud", 200);
    }
    public function descargarDocumentosSat(Request $request){
        //try {
            $lib = new LibSat();
            $id_empresa = $request["id_empresa"];
            $nombre_archivo_sat = $request["nombre_archivo_sat"];
            $pass = DB::table("gen_cat_empresa")
            ->select("firma_contra")
            ->where("id_empresa", $id_empresa)
            ->first();
            $datos = [
                "id_empresa" => $id_empresa,
                "password" => $pass->firma_contra,
                "archivos" => [
                    $nombre_archivo_sat
                ],
            ];
            $servicio = $lib->descargar($datos);
            $myRequest = new \Illuminate\Http\Request();
            $myRequest->setMethod('POST');
            $myRequest->request->add(['id_empresa' => $id_empresa]);
            $myRequest->request->add(['data' => $servicio]);
            $myRequest->request->add(['extension' => "application/x-zip-compressed"]);
            $myRequest->request->add(['usuario_c' => 1]);

            $this->altaBobedaXML($myRequest);

    }
    public function getSolicitudesSat($id_empresa){
        try {
            $data = DB::table('con_solicitudes_sat as css')
            ->join('gen_cat_statu as es', 'es.id_statu', '=', 'css.id_estatus')
            ->select("css.*", "es.status")
            ->where("css.id_empresa", $id_empresa)
            ->get();
            return $this->crearRespuesta(1, $data, 200);
        } catch (\Throwable $th) {
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $th->getMessage(),200);
        }
    }

    public function consumeMetodo($res)
    {
        #region Validaciones
            if(empty($res["data"]) || !is_string($res["data"])){
                return $this->crearRespuesta(2,"Error al subir documento",200);
            }
            if(empty($res["id_empresa"]) || $res["id_empresa"] == 0){
                return $this->crearRespuesta(2,"No se ha seleccionado la empresa",200);
            }
        #endregion

        $cfdiOrZip = base64_decode($res["data"]);

        if($res["extension"] == "application/x-zip-compressed"){
            //Definimos la ruta de alamcenado
            $path = storage_path("MisArchivosTemp");
            //Almancenos el zip en base64 en archivo temp
            file_put_contents($path."/temp_zip.zip",$cfdiOrZip);
            $zip = new ZipArchive;
            //Abrimos el .zip temporal
            $comprimido = $zip->open($path."/temp_zip.zip");
            if ($comprimido=== TRUE) {
                //Lo descomprimimos
                $zip->extractTo($path);
                //Lo cerramos
                $zip->close();
                //Eliminamos el archivo .zip temp
                unlink($path."/temp_zip.zip");

                //Buscamos el directorio
                $gestor = opendir($path);
                $errores = [];
                //Recorremos el directorio
                while (($archivo = readdir($gestor)) !== false){
                    $ruta_completa = $path."/".$archivo;
                    $extension = pathinfo($ruta_completa, PATHINFO_EXTENSION);
                    if(file_exists($ruta_completa) && $extension == "xml"){
                        $xml = file_get_contents($ruta_completa);
                        $result = $this->insertarXML($xml,$res["id_empresa"],$res["usuario_c"]);
                        if(!$result["ok"]){
                            array_push($errores,$result["message"]);
                        }
                        unlink($ruta_completa);
                    }
                }
                if(count($errores) > 0){
                    return $this->crearRespuesta(1,$errores,200);
                }
                return $this->crearRespuesta(1,"Zip insertado",200);
            }
            return $this->crearRespuesta(2,"Error al descomprimir el arhivo .zip",200);
        }

        //Insertar XML normal
        $result =  $this->insertarXML($cfdiOrZip,$res["id_empresa"],$res["usuario_c"]);
        if(!$result["ok"]){
            return $this->crearRespuesta(2,$result["message"],200);
        }
        return $this->crearRespuesta(1,$result["data"],200);
    }

    
    public function altaBobedaXML(Request $res)
    {
        return $this->consumeMetodo($res);
    }

    function insertarXML($xml,$id_empresa,$usuario){
        try{
            $rfc = "";
            $datos_empresa = DB::table('gen_cat_empresa')->where("id_empresa",$id_empresa)->first();
            if($datos_empresa){
                $rfc = $datos_empresa->rfc;
            }
            $sXML = $xml."";
            $xml = simplexml_load_string($xml);
            $namespaces = $xml->getNamespaces(true);
            $xml->registerXPathNamespace('c', $namespaces['cfdi']);
            $xml->registerXPathNamespace('t', $namespaces['tfd']);
            $xml->registerXPathNamespace('n', $namespaces['nomina12']);

            $bobedaXML = new BobedaXML();
            $bobedaXML->id_empresa = $id_empresa;
            $bobedaXML->xml = $sXML;
            

            foreach($xml->xpath('//c:Comprobante') as $dato){
                $bobedaXML->tipo_combrobante = $dato['TipoDeComprobante'];
                $bobedaXML->subtotal = $dato['SubTotal'];
                $bobedaXML->total = $dato['Total'];
                $bobedaXML->moneda = $dato['Moneda'];
                $bobedaXML->descuento = $dato['Descuento'];
            }

            if(empty($bobedaXML->tipo_combrobante)){
                return [ "ok" => false, "message" => "No se ha encontrado el TipoDeComprobante de este XML"];
            }

            if($bobedaXML->moneda != "MXN"){
                $tipo_cambio = DB::table("sat_CatMoneda")->where("clave_moneda",$bobedaXML->moneda)->first()->tipo_cambio;
                $bobedaXML->cambio_subtotal = doubleval($bobedaXML->subtotal) * doubleval($tipo_cambio);
                $bobedaXML->cambio_total = doubleval($bobedaXML->total) * doubleval($tipo_cambio);;
                $bobedaXML->tipo_cambio = $tipo_cambio;
            }
            
            foreach($xml->xpath('//c:Receptor') as $dato){
                $bobedaXML->rfc = $dato['Rfc'];
                $bobedaXML->nombre = "";
                if(isset($dato['Nombre'])){
                    $bobedaXML->nombre = $dato['Nombre'];
                }
            }

            foreach($xml->xpath('//t:TimbreFiscalDigital') as $dato){
                $bobedaXML->uuid = $dato['UUID'];
            }

            if($bobedaXML->tipo_combrobante == "N"){
                foreach($xml->xpath('//n:Nomina') as $dato){
                    $bobedaXML->fecha_pago = $dato['FechaPago'];
                    $bobedaXML->fecha_inicial_pago = $dato['FechaInicialPago'];
                    $bobedaXML->fecha_final_pago = $dato['FechaFinalPago'];
                }
                foreach($xml->xpath('//n:Receptor') as $dato){
                    $bobedaXML->curp = $dato['Curp'];
                    $bobedaXML->num_seguro = $dato['NumSeguridadSocial'];
                    $bobedaXML->salario_diario = $dato['SalarioDiarioIntegrado'];
                    $bobedaXML->salario_base = $dato['SalarioBaseCotApor'];
                }
            }

            //Validar UUID
            if(empty($bobedaXML->uuid)){
                return [ "ok" => false, "message" => "No se ha encontrado el UUID de este XML"];
            }

            $validar = DB::table('bobeda_xml')->where("uuid",$bobedaXML->uuid)->first();
            if($validar){
                return [ "ok" => false, "message" => "El XML con UUID ".$bobedaXML->uuid . " ya se encuentra registrado" ];
            }

            
            $rfc_emisor = "";

            foreach($xml->xpath('//c:Emisor') as $dato){
                $rfc_emisor = $dato['Rfc'];
            } 

            $bobedaXML->emitidos = false;
            if($rfc == $rfc_emisor){
                $bobedaXML->emitidos = true;
            }

            $bobedaXML->id_estatus = 1;
            $bobedaXML->fecha_creacion = $this->getHoraFechaActual();
            $bobedaXML->usuario_creacion = $usuario;
            $bobedaXML->activo = 1;
            $bobedaXML->save();
            

            if($bobedaXML->tipo_combrobante == "N"){
                $detalle_nomina = new DetalleNomina();
                foreach($xml->xpath('//n:Percepciones/n:Percepcion') as $percepcion){
                    $detalle_nomina->id_bobeda = $bobedaXML->id_bobeda;
                    $detalle_nomina->tipo = 'P';
                    $detalle_nomina->clave = $percepcion['Clave'];
                    $detalle_nomina->concepto = $percepcion['Concepto'];
                    $detalle_nomina->importe = $percepcion['ImporteExcento'];
                    $detalle_nomina->importe_gravado = $percepcion['ImporteGravado'];
                    $detalle_nomina->clave_tipo = $percepcion['TipoPercepcion'];
                    $detalle_nomina->fecha_creacion = $this->getHoraFechaActual();
                    $detalle_nomina->usuario_creacion = $usuario;
                    $detalle_nomina->save();
                }
                foreach($xml->xpath('//n:Deducciones/n:Deduccion') as $deduccion){
                    $detalle_nomina = new DetalleNomina();
                    $detalle_nomina->id_bobeda = $bobedaXML->id_bobeda;
                    $detalle_nomina->tipo = 'D';
                    $detalle_nomina->clave = $deduccion['Clave'];
                    $detalle_nomina->concepto = $deduccion['Concepto'];
                    $detalle_nomina->importe = $deduccion['Importe'];
                    $detalle_nomina->importe_gravado = "";
                    $detalle_nomina->clave_tipo = $deduccion['TipoDeduccion'];
                    $detalle_nomina->fecha_creacion = $this->getHoraFechaActual();
                    $detalle_nomina->usuario_creacion = $usuario;
                    $detalle_nomina->save();
                }
                foreach($xml->xpath('//n:OtrosPagos/n:OtroPago') as $otros){
                    $detalle_nomina = new DetalleNomina();
                    $detalle_nomina->id_bobeda = $bobedaXML->id_bobeda;
                    $detalle_nomina->tipo = 'O';
                    $detalle_nomina->clave = $otros['Clave'];
                    $detalle_nomina->concepto = $otros['Concepto'];
                    $detalle_nomina->importe = $otros['Importe'];
                    $detalle_nomina->importe_gravado = "";
                    $detalle_nomina->clave_tipo = $otros['TipoOtroPago'];
                    $detalle_nomina->fecha_creacion = $this->getHoraFechaActual();
                    $detalle_nomina->usuario_creacion = $usuario;
                    $detalle_nomina->save();
                }
            }
            return [ "ok" => true, "data" => "XML almacendo con éxito" ];
        } catch(\Error $e){
            return [ "ok" => false, "message" => "Ha ocurrido un error : " . $e->getMessage() ];
        }
    }
}
