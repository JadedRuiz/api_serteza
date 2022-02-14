<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Exports\FacturaExport;
use ZipArchive;
use App\Models\Factura;
use App\Models\DetFactura;

class FacturacionController extends Controller
{
    public function facObtenerFolio($id_empresa)
    {
        $folio = Factura::select("folio")
        ->where("id_empresa",$id_empresa)
        ->orderBy("folio","desc")
        ->first();
        if($folio){
            return $this->crearRespuesta(1,$folio,200);
        }
        return $this->crearRespuesta(2,"Aun no se tiene facturas de esta empresa",200);
    }
    public function facAltaFactura(Request $res)
    {
        $facturador = new FacturaExport();
        return $facturador->generarTimbrado([]);
        //Validaciones
        // try{
        //     $fecha = $this->getHoraFechaActual();
        //     $usuario_creacion = $res["usuario_creacion"];
        //     $factura = new Factura();
        //     $factura->id_empresa = $res["id_empresa"];
        //     $factura->id_catclientes = $res["id_cliente"];
        //     $factura->id_serie = $res["id_serie"];
        //     $factura->folio = $res["folio"];
        //     $factura->id_formapago = $res["id_formapago"];
        //     $factura->id_metodopago = $res["id_metodopago"];
        //     $factura->numero_cuenta = $res["numero_cuenta"];
        //     $factura->id_tipomoneda = $res["id_tipomoneda"];
        //     $factura->id_usocfdi = $res["id_usocfdi"];
        //     $factura->id_tipocomprobante = $res["tipo_comprobante"];
        //     $factura->condicion_pago = $res["condiciones"];
        //     $factura->tipo_cambio = $res["tipo_cambio"];
        //     $factura->observaciones = $res["observaciones"];
        //     $factura->usa_ine = $res["usa_ine"];
        //     $factura->usa_cataporte = $res["usa_cataporte"];
        //     $factura->subtotal = $res["subtotal"];
        //     $factura->descuento = $res["descuento_t"];
        //     $factura->iva = $res["iva_t"];
        //     $factura->ieps = $res["ieps_t"];
        //     $factura->otros = $res["otros_t"];
        //     $factura->total = $res["total"];
        //     $factura->fecha_creacion = $fecha;
        //     $factura->usuario_creacion = $usuario_creacion;
        //     $factura->activo = 1;
        //     $factura->save();
        //     $id_factura = $factura->id_factura;
        //     $conceptos = $res["conceptos"];
        //     foreach($conceptos as $concepto){
        //         $detfactura = new DetFactura();
        //         $detfactura->id_factura = $id_factura;
        //         $detfactura->id_concepto = $concepto["id_concepto"];
        //         $detfactura->descripcion = $concepto["descripcion"];
        //         $detfactura->cantidad = $concepto["cantidad"];
        //         $detfactura->importe = $concepto["precio"];
        //         $detfactura->descuento = $concepto["descuento"];
        //         $detfactura->iva = $concepto["iva"];
        //         $detfactura->ieps = $concepto["ieps"];
        //         $detfactura->otros_imp = $concepto["otros"];
        //         $detfactura->subtotal = $concepto["neto"];
        //         $detfactura->total = $concepto["importe"];
        //         $detfactura->fecha_creacion = $fecha;
        //         $detfactura->usuario_creacion = $usuario_creacion;
        //         $detfactura->activo = 1;
        //         $detfactura->save();
        //     }
        //     return $this->crearRespuesta(1,"Factura dado de alta",200);
        // }catch(Throwable $e){
        //     return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        // }
    }
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
            ->select("empleado as nombre","uuid",DB::raw('DATE_FORMAT(fecha_pago,"%d-%m-%Y") as fecha_pago'),DB::raw('DATE_FORMAT(fecha_timbrado,"%d-%m-%Y") as fecha_timbrado'), "xml","id_empresa")
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
                    // Creamos un instancia de la clase ZipArchive
                    $zip = new ZipArchive();
                    $archivo = storage_path('factura') ."/masiva_temp.zip";
                    // Creamos y abrimos un archivo zip temporal
                    if($zip->open($archivo,ZipArchive::CREATE) == true){
                        foreach($facturas as $factura){
                            $reporte = new FacturaExport();
                            $respuesta =  $reporte->generarReporteFactura([
                                "id_empresa" => $factura->id_empresa,
                                "xml" => $factura->xml,
                                "tipo" => true
                            ]);
                            $zip->addFromString($factura->uuid.".xml",$factura->xml);
                            $zip->addFromString($factura->uuid.".pdf",base64_decode($respuesta["pdf"]));
                        }
                        $zip->close();
                        $base_64 = base64_encode(file_get_contents($archivo));
                        return $this->crearRespuesta(1,$base_64,200);
                    }
                }catch(Throwable $e){
                    return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
                }
            }
        }
    }
}
