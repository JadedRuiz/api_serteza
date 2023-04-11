<?php

namespace App\Lib;
use App\Models\Empresa;
use Illuminate\Support\Facades\DB;
use DOMDocument;
use XSLTProcessor;
use Genkgo\Xsl\XsltProcessor as XslProc;
use Genkgo\Xsl\Cache\NullCache;

class Sello {

    public function sellar($datos)
    {
		 
        //Recuperar datos empresa
        $datos_empresa = Empresa::select("no_certificado","certificado","key")
        ->where("id_empresa",$datos["id_empresa"])
        ->first();
        //Validar existencia de empresa
        if(!$datos_empresa){
            return ["ok"=>false, "message" => "No se ha podido recuperar la informaci贸n de la empresa emisora"];
        }		
        //Variables globales del m茅todo
        $key = $datos_empresa->key;
        $cer = $datos_empresa->certificado;
        $numcer = $datos_empresa->no_certificado;
        $private = openssl_pkey_get_private(file_get_contents(storage_path("empresa")."/".$key));
        $certificado = str_replace(array('\n', '\r'), '', base64_encode(file_get_contents(storage_path("empresa")."/".$cer)));

		
        
        $cfdi = $this->generarXML($datos,$numcer);
		//return ["ok"=>false, "message" => $cfdi];
        
        try{

            $xdoc = new DOMDocument("1.0","UTF-8");

            $xdoc->loadXML($cfdi);

            $XSL = new DOMDocument();
            $path = storage_path("utilerias")."/cadenaoriginal_4_0.xslt";
            $XSL->load($path);
            $proc = new XslProc(new NullCache());
            $proc->importStylesheet($XSL);
        }catch( Throwable $e){
            return ["ok"=>false, "data" => "XML invalido : " . $e->getMessage()];
        }
        
        $cadena_original = $proc->transformToXML($xdoc);
        $datoss = explode("|", $cadena_original);

        $cadena_original = "";

        for ($i = 1; $i < count($datoss); $i++) {
            $cadena_original = $cadena_original . "|" . trim($datoss[$i]);
        }
        // return ["ok"=>true, "data" => $cadena_original];
        $sig = "";
        openssl_sign($cadena_original, $sig, $private, OPENSSL_ALGO_SHA256);
        $sello = base64_encode($sig);
        $c = $xdoc->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/4', 'Comprobante')->item(0);
        $c->setAttribute('Sello', $sello);
        $c->setAttribute('Certificado', $certificado);
        $xmlsello = $xdoc->saveXML();

        return ["ok"=>true, "data" => $xmlsello];
    }
    public function generarXML($datos,$numcer)
    {
        //Recuperaci贸n Claves SAT
        $metodopago = DB::table('sat_MetodoPago')
        ->select("clave_pago")
        ->where("id_metodopago",$datos["id_metodopago"])
        ->first()
        ->clave_pago;
        $tipocomprobante = DB::table('sat_TipoComprobante')
        ->select("clave_comprobante")
        ->where("id_tipocomprobante",$datos["tipo_comprobante"])
        ->first()
        ->clave_comprobante;
        $formapago = DB::table('sat_FormaPago')
        ->select("forma_pago")
        ->where("id_formapago",$datos["id_formapago"])
        ->first()
        ->forma_pago;
        $fecha = date("Y-m-d")."T".date("H:i:s");
		// fecha menos un dia
		//$date_future = strtotime('-1 day', strtotime(date("Y-m-d")));
		//$fecha = date('Y-m-d', $date_future);
		//$fecha = $fecha."T".date("H:i:s");
	
		//$fecha = date("Y-m-d")."T".date("H:i:s");
        $periodicidad = date("m");
        $ejercicio = date("Y");
        $lugarexpedicion = "97000"; //CP
        $moneda = "MXN";
        $serie = DB::table('fac_catseries')
        ->select("serie")
        ->where("id_serie",$datos["id_serie"])
        ->first()
        ->serie;
        $datos_emisor = Empresa::select("rfc","regimen_fiscal","razon_social as empresa","gcd.calle","gcd.descripcion","gcd.numero_exterior","gcd.numero_interior","gcd.colonia","gcd.municipio","gce.estado","gce.pais","gcd.codigo_postal")
        ->leftJoin("gen_cat_direccion as gcd","gcd.id_direccion","=","gen_cat_empresa.id_direccion")
        ->leftJoin("gen_cat_estados as gce","gce.id_estado","=","gcd.estado")
        ->where("id_empresa",$datos["id_empresa"])
        ->first();
        $regimenemi = $datos_emisor->regimen_fiscal;
		$total_ba = 0;
		//error_log(print_r($regimenemi, true), 3, "emisor_xml.log");
		
		
        $datos_receptor = DB::table('fac_catclientes')
        ->select("rfc","razon_social","gcd.calle","gcd.descripcion","gcd.numero_exterior","gcd.numero_interior","gcd.colonia","gcd.municipio","gce.estado","gce.pais","gcd.codigo_postal", "srf.clave as regimenfiscal")
        ->join("sat_regimenesfiscales as srf", "fac_catclientes.id_regimenfiscal", "=", "srf.id_regimenfiscal")
        ->leftJoin("gen_cat_direccion as gcd","gcd.id_direccion","=","fac_catclientes.id_direccion")
        ->leftJoin("gen_cat_estados as gce","gce.id_estado","=","gcd.estado")
        ->where("id_catclientes",$datos["id_cliente"])
        ->first();
        $usoCFDI = DB::table('sat_UsoCFDI')
        ->select("clave_cfdi")
        ->where("id_usocfdi",$datos["id_usocfdi"])
        ->first()
        ->clave_cfdi;

        //XML
        $xml = "";
        $implocal = "";
		$schema_location = "";
        if($datos["otros_t"] != "" && intval($datos["otros_t"]) > 0){
            $implocal = ' xmlns:implocal="http://www.sat.gob.mx/implocal" ';
			$schema_location = ' http://www.sat.gob.mx/implocal http://www.sat.gob.mx/sitio_internet/cfd/implocal/implocal.xsd';
        }
        $xml = $xml . '<?xml version="1.0" encoding="UTF-8"?>';
        
        
        
  		
        $exportacion = "01";
        $xml = $xml . '<cfdi:Comprobante '.$implocal.' xsi:schemaLocation="http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd'.$schema_location.'" xmlns:cfdi="http://www.sat.gob.mx/cfd/4" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" LugarExpedicion="' . $lugarexpedicion . '" MetodoPago="' . $metodopago . '" CondicionesDePago="' . $datos["condiciones"] . '" TipoDeComprobante="' . $tipocomprobante .'" Exportacion="'.$exportacion . '" Total="' .  number_format(round(str_replace(',','',$datos["total"]),2),2,'.','') . '" Descuento="' .  number_format(round(str_replace(',','',$datos["descuento_t"]),2),2,'.','') . '" SubTotal="' .  number_format(round(str_replace(',','',$datos["importe"]),2),2,'.','') . '" Certificado="" NoCertificado="'.$numcer.'" FormaPago="' . $formapago . '" Sello=""  Fecha="' . $fecha . '" Moneda="' . $moneda . '" Folio="' . $datos["folio"] . '" Serie="' . $serie;
        $xml = $xml .'" Version="4.0">';
        $domicilio_receptor = $datos_receptor->codigo_postal;
        if($datos_receptor->rfc == "XAXX010101000"){
            $xml = $xml .'<cfdi:InformacionGlobal Periodicidad="01" Meses="'.$periodicidad.'" Año="' . $ejercicio .'" />';
            $usoCFDI = "S01";
            $domicilio_receptor = $lugarexpedicion;
        }
        if($datos["tiene_rela"]){
            $xml .= '<cfdi:CfdiRelacionados TipoRelacion="'.$datos["relaciones"]["id_tiporela"].'" >';
                $xml .= '<cfdi:CfdiRelacionado UUID="'.strtoupper($datos["relaciones"]["folio_fiscal"]).'" />';
            $xml .= '</cfdi:CfdiRelacionados>';
        }
        $xml = $xml . '<cfdi:Emisor Rfc="' . trim($datos_emisor->rfc) . '" Nombre="' . $datos_emisor->empresa . '" RegimenFiscal="'.$regimenemi.'" />';
        $xml = $xml . '<cfdi:Receptor Rfc="' . $datos_receptor->rfc . '" Nombre="' . $datos_receptor->razon_social . '" DomicilioFiscalReceptor="'.$domicilio_receptor. '" RegimenFiscalReceptor="'.$datos_receptor->regimenfiscal. '" UsoCFDI="'.$usoCFDI.'" />';
        
        $xml = $xml . '<cfdi:Conceptos>';
        $conceptos_array = [];
        $sumaBase = 0.00;
		
        foreach($datos["conceptos"] as $concepto){
            array_push($conceptos_array,$concepto["id_concepto"]);
            $concepto_info = DB::table('fac_catconceptos as fcc')
            ->select("scp.ClaveProdServ","sum.ClaveUnidad","sum.Descripcion as unidad","soi.clave as objetoimp","fcc.clave_producto")
            ->join("sat_UnidadMedida as sum","sum.id_UnidadMedida","=","fcc.id_UnidadMedida")
            ->join("sat_ClaveProdServ as scp","scp.id_ClaveProdServ","=","fcc.id_ClaveProdServ")
            ->join("sat_objetoimp as soi", "soi.id_objetoimp","=", "fcc.id_objetoimp")
            ->where("fcc.id_concepto_empresa",$concepto["id_concepto"])
            ->first();
            $xml = $xml . '<cfdi:Concepto Importe="' . number_format(round($concepto["importe"],2),2,'.','') . '" NoIdentificacion="' . $concepto_info->clave_producto . '" ValorUnitario="' .  number_format(round($concepto["precio"],2),2,'.','') . '" Descripcion="' . $concepto["descripcion"] . '" Unidad="' . $concepto_info->unidad . '" Cantidad="' . $concepto["cantidad"] . '" Descuento="'.$concepto["descuento"].'" ClaveProdServ="' .$concepto_info->ClaveProdServ. '" ClaveUnidad="'.$concepto_info->ClaveUnidad. '" ObjetoImp="'.$concepto_info->objetoimp.'">';
            $band_impuestos = false;
            $impuestos = $xml."<cfdi:Impuestos>";
            if(floatval($concepto["iva"]) > 0 || floatval($concepto["ieps"]) > 0){
                $band_impuestos = true;
                $impuestos .= '<cfdi:Traslados>';
                if(floatval($concepto["iva"]) > 0){
                    $porcentaje = number_format(round((floatval($concepto["iva_porcent"])/100),2),6,'.','');
                    $impuestos .= '<cfdi:Traslado Impuesto="002" TasaOCuota="'.$porcentaje.'" Importe="' . number_format(round($concepto["iva"],2),2,'.','') . '" TipoFactor="Tasa" Base="'. number_format(round($concepto["importe"],2),2,'.','').'"></cfdi:Traslado>';
                }
                if(floatval($concepto["ieps"]) > 0){
                    $porcentaje = number_format(round((floatval($concepto["ieps_porcent"])/100),2),6,'.','');
                    $impuestos .= '<cfdi:Traslado Impuesto="003" TasaOCuota="'.$porcentaje.'" Importe="' . number_format(round($concepto["ieps"],2),2,'.','') . '" TipoFactor="Tasa" Base="'. number_format(round($concepto["importe"],2),2,'.','').'"></cfdi:Traslado>';
                }
                $impuestos .= '</cfdi:Traslados>';
            }
            if(floatval($concepto["iva_r"]) > 0 || floatval($concepto["isr_r"]) > 0){
                $band_impuestos = true;
                $impuestos .= '<cfdi:Retenciones>';
                if(floatval($concepto["iva_r"]) > 0){
                    $porcentaje = number_format(round((floatval($concepto["iva_r_porcent"])/100),2),6,'.','');
                    $impuestos .= '<cfdi:Retencion Impuesto="002" TasaOCuota="'.$porcentaje.'" Importe="' . number_format(round($concepto["iva_r"],2),2,'.','') . '" TipoFactor="Tasa" Base="'. number_format(round($concepto["importe"],2),2,'.','').'"></cfdi:Retencion>';
                }
                if(floatval($concepto["isr_r"]) > 0){
                    $porcentaje = number_format(round((floatval($concepto["isr_r_porcent"])/100),2),6,'.','');
                    $impuestos .= '<cfdi:Retencion Impuesto="001" TasaOCuota="'.$porcentaje.'" Importe="' . number_format(round($concepto["isr_r"],2),2,'.','') . '" TipoFactor="Tasa" Base="'. number_format(round($concepto["importe"],2),2,'.','').'"></cfdi:Retencion>';
                }
                $impuestos .= '</cfdi:Retenciones>';
            }
            $band_impuestos ? $xml = $impuestos."</cfdi:Impuestos>" : $xml = $xml.'';

            $xml = $xml . '</cfdi:Concepto>';
        }
        $xml = $xml . '</cfdi:Conceptos>';
		
        $band_impuestos = false;
        $string_impuestos = "";
        $total_impretenido = "";
        $totalimptrasladado="";
        if(floatval($datos["iva_r_t"]) >0 || floatval($datos["isr_r_t"]) >0){
            $band_impuestos = true;
            $string_impuestos .= '<cfdi:Retenciones>';
            $total_impretenido = floatval(str_replace(',','',$datos["iva_r_t"]))+floatval(str_replace(',','',$datos["isr_r_t"]));
            if(floatval($datos["isr_r_t"]) >0){
                $string_impuestos .= '<cfdi:Retencion Impuesto="001" Importe="'.number_format(round($datos["isr_r_t"],2),2,'.','').'" />';
            }
            if(floatval($datos["iva_r_t"]) >0){
                $string_impuestos .= '<cfdi:Retencion Impuesto="002" Importe="'.number_format(round($datos["iva_r_t"],2),2,'.','').'" />';
            }
            
            $string_impuestos .= '</cfdi:Retenciones>';          
        }
        if(floatval($datos["iva_t"]) > 0 || floatval($datos["ieps_t"]) >0){
            $band_impuestos = true;
            $totalimptrasladado = floatval(str_replace(',','',$datos["iva_t"]))+floatval(str_replace(',','',$datos["ieps_t"]));
            $base = "";
            $string_impuestos .= '<cfdi:Traslados>';
            $base = 'Base="'.number_format(round(str_replace(',','',$datos["importe"]),2),2,'.','').'"';
            if(floatval($datos["iva_t"]) > 0){
                $string_impuestos .= '<cfdi:Traslado '.$base.' Impuesto="002" TasaOCuota="0.160000" Importe="' . number_format(round(str_replace(',','',$datos["iva_t"]),2),2,'.','') . '" TipoFactor="Tasa" />';
            }
            if(floatval($datos["ieps_t"]) >0){
                $string_impuestos .= '<cfdi:Traslado "'.$base.'" Impuesto="003" TasaOCuota="0.160000" Importe="' . number_format(round(str_replace(',','',$datos["iva_t"]),2),2,'.','') . '" TipoFactor="Tasa" />';
            }
            $string_impuestos .= '</cfdi:Traslados>';
        }
        
        $band_impuestos ? $xml = $xml.'<cfdi:Impuestos TotalImpuestosRetenidos="'. number_format(round(str_replace(',','',$total_impretenido),2),2,'.','').'" TotalImpuestosTrasladados="'.number_format(round(str_replace(',','',$totalimptrasladado),2),2,'.','').'">'.$string_impuestos.'</cfdi:Impuestos>' 
        : $xml = $xml.'';
        $xml = $xml . '<cfdi:Complemento>';
        //Otros impuestos
        if($datos["otros_t"] != "0" && intval($datos["otros_t"]) > 0){
            $string_impuestos = "";
            $datos_conceptos = DB::table('fac_catconceptos')
            ->select("otros_imp","tipo_otros","nombre_otros")
            ->whereIn("id_concepto_empresa",$conceptos_array)
            ->get();
            $total_otros_r = 0.00;
            $total_otros_t = 0.00;
            foreach($datos_conceptos as $dato_concepto){
                $porcentage = (floatval($dato_concepto->otros_imp)/100.00);
                $importe = $porcentage * floatval($datos["subtotal"]);
				$impuestolocal = $sumaBase * $porcentage;
				
                if($dato_concepto->tipo_otros == "T"){
                    $total_otros_t += $importe;
                    $string_impuestos .= '<implocal:TrasladosLocales ImpLocTrasladado="'.$dato_concepto->nombre_otros.'" TasadeTraslado="'.$porcentage.'" Importe="'.number_format(round(str_replace(',','',$impuestolocal.""),2),2,'.','').'" />';
                }
                if($dato_concepto->tipo_otros == "R"){
                    $total_otros_r += $importe;
                    $string_impuestos .= '<implocal:TrasladosLocales ImpLocRetencion="'.$dato_concepto->nombre_otros.'" TasadeRetencion="'.$porcentage.'" Importe="'.number_format(round(str_replace(',','',$impuestolocal.""),2),2,'.','').'" />';
                }
            }
            $xml = $xml . '<implocal:ImpuestosLocales version="1.0" TotaldeRetenciones="'.number_format(round(str_replace(',','',$total_otros_r.""),2),2,'.','').'" TotaldeTraslados="'.number_format(round(str_replace(',','',$impuestolocal.""),2),2,'.','').'">';
                $xml = $xml.$string_impuestos;
            $xml = $xml . '</implocal:ImpuestosLocales>';
        }
        $xml = $xml.'</cfdi:Complemento>';
        $xml = $xml . '</cfdi:Comprobante>';

	     error_log(print_r($xml, true), 3, "xml_log.log");

        //$this->write_to_console($xml);
        return $xml;
    }

    function write_to_console($data) {
        $console = $data;
        if (is_array($console))
        $console = implode(',', $console);
       
        echo "<script>console.log('Console: " . $console . "' );</script>";
       }
}
?>