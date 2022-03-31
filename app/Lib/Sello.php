<?php

namespace App\Lib;
use App\Models\Empresa;
use Illuminate\Support\Facades\DB;
use DOMDocument;
use XSLTProcessor;

class Sello {

    public function sellar($datos)
    {
        //Recuperar datos empresa
        $datos_empresa = Empresa::select("no_certificado","certificado","key")
        ->where("id_empresa",$datos["id_empresa"])
        ->first();
        //Validar existencia de empresa
        if(!$datos_empresa){
            return ["ok"=>false, "message" => "No se ha podido recuperar la información de la empresa emisora"];
        }
        //Variables globales del método
        $key = $datos_empresa->key;
        $cer = $datos_empresa->certificado;
        $numcer = $datos_empresa->no_certificado;
        $private = openssl_pkey_get_private(file_get_contents(storage_path("empresa")."/".$key));
        $certificado = str_replace(array('\n', '\r'), '', base64_encode(file_get_contents(storage_path("empresa")."/".$cer)));

        $cfdi = $this->generarXML($datos,$numcer);
        //$cfdi='cachktest_0.xml';
        
        try{
            $xdoc = new DOMDocument("1.0","UTF-8");
            $xdoc->loadXML($cfdi);

            $XSL = new DOMDocument();
            $path = storage_path("utilerias")."/cadenaoriginal_3_3.xslt";
            $XSL->load($path);

            $proc = new XSLTProcessor;
            $proc->importStyleSheet($XSL);
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

        $c = $xdoc->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Comprobante')->item(0);
        $c->setAttribute('Sello', $sello);
        $c->setAttribute('Certificado', $certificado);
        // $c->setAttribute('NoCertificado', $numcer);
        $xmlsello = $xdoc->saveXML();
        // file_put_contents($numcer . ".xml", $xmlsello);
        return ["ok"=>true, "data" => $xmlsello];
    }
    public function generarXML($datos,$numcer)
    {
        //Recuperación Claves SAT
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
        $lugarexpedicion = "97144"; //CP
        $moneda = "MXN";
        $serie = DB::table('fac_catseries')
        ->select("serie")
        ->where("id_serie",$datos["id_serie"])
        ->first()
        ->serie;
        $datos_emisor = Empresa::select("rfc","empresa","gcd.calle","gcd.descripcion","gcd.numero_exterior","gcd.numero_interior","gcd.colonia","gcd.municipio","gce.estado","gce.pais","gcd.codigo_postal")
        ->leftJoin("gen_cat_direccion as gcd","gcd.id_direccion","=","gen_cat_empresa.id_direccion")
        ->leftJoin("gen_cat_estados as gce","gce.id_estado","=","gcd.estado")
        ->where("id_empresa",$datos["id_empresa"])
        ->first();
        $regimenemi = "601";
        $datos_receptor = DB::table('fac_catclientes')
        ->select("rfc","razon_social","gcd.calle","gcd.descripcion","gcd.numero_exterior","gcd.numero_interior","gcd.colonia","gcd.municipio","gce.estado","gce.pais","gcd.codigo_postal")
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
        $xml = $xml . '<?xml version="1.0" encoding="UTF-8"?>';
        $xml = $xml . '<cfdi:Comprobante xsi:schemaLocation="http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv33.xsd" xmlns:cfdi="http://www.sat.gob.mx/cfd/3" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" LugarExpedicion="' . $lugarexpedicion . '" MetodoPago="' . $metodopago . '" CondicionesDePago="' . $datos["condiciones"] . '" TipoDeComprobante="' . $tipocomprobante . '" Total="' .  number_format(round($datos["total"],2),2) . '" Descuento="' .  number_format(round($datos["descuento_t"],2),2) . '" SubTotal="' .  number_format(round($datos["subtotal"],2),2) . '" Certificado="" NoCertificado="'.$numcer.'" FormaPago="' . $formapago . '" Sello=""  Fecha="' . $fecha . '" Moneda="' . $moneda . '" Folio="' . $datos["folio"] . '" Serie="' . $serie . '" Version="3.3">
  		<cfdi:Emisor Rfc="' . trim($datos_emisor->rfc) . '" Nombre="' . $datos_emisor->empresa . '" RegimenFiscal="'.$regimenemi.'" />';

        $xml = $xml . '<cfdi:Receptor Rfc="' . $datos_receptor->rfc . '" Nombre="' . $datos_receptor->razon_social . '" UsoCFDI="'.$usoCFDI.'" />';
        $xml = $xml . '<cfdi:Conceptos>';
        foreach($datos["conceptos"] as $concepto){
            $concepto_info = DB::table('fac_catconceptos as fcc')
            ->select("scp.ClaveProdServ","sum.ClaveUnidad","sum.Descripcion as unidad")
            ->join("sat_UnidadMedida as sum","sum.id_UnidadMedida","=","fcc.id_UnidadMedida")
            ->join("sat_ClaveProdServ as scp","scp.id_ClaveProdServ","=","fcc.id_ClaveProdServ")
            ->where("fcc.id_concepto_empresa",$concepto["id_concepto"])
            ->first();
            $codigo = "PROD";
            $xml = $xml . '<cfdi:Concepto Importe="' . number_format(round($concepto["neto"],2),2) . '" NoIdentificacion="' . $codigo . '" ValorUnitario="' .  number_format(round($concepto["precio"],2),2) . '" Descripcion="' . $concepto["descripcion"] . '" Unidad="' . $concepto_info->unidad . '" Cantidad="' . $concepto["cantidad"] . '" Descuento="'.$concepto["descuento"].'" ClaveProdServ="' .$concepto_info->ClaveProdServ. '" ClaveUnidad="'.$concepto_info->ClaveUnidad.'">';
            if($concepto["iva"] != "0"){
                $totalimptrasladado = $concepto["ieps"] + $concepto["iva"];
                $xml = $xml . '<cfdi:Impuestos>';
                    $xml = $xml . '<cfdi:Traslados>';
                        $xml = $xml . '<cfdi:Traslado Impuesto="002" TasaOCuota="0.160000" Importe="' . number_format(round($concepto["iva"],2),2) . '" TipoFactor="Tasa" Base="'. number_format(round($concepto["neto"],2),2).'"></cfdi:Traslado>';
                        if ($concepto["ieps"] != "0") {
                            $xml = $xml . '<cfdi:Traslado Impuesto="003" TasaOCuota="' . $concepto["ieps_porcent"] . '" Importe="' . number_format(round($concepto["ieps"],2),2) . '" TipoFactor="Tasa" Base="'. number_format(round($concepto["neto"],2),2).'"></cfdi:Traslado>'; 
                        }
                    $xml = $xml . '</cfdi:Traslados>';
                $xml = $xml . '</cfdi:Impuestos>';
            }else {
                $xml = $xml . '<cfdi:Impuestos/>
                ';
            }
            

            $xml = $xml . '</cfdi:Concepto>';
        }
        $xml = $xml . '</cfdi:Conceptos>';
        if ($datos["iva_t"] != "0") {
            $totalimptrasladado = floatval($datos["iva_t"])+floatval($datos["ieps_t"]);
            $xml = $xml . '<cfdi:Impuestos TotalImpuestosTrasladados="' . number_format(round($totalimptrasladado,2),2) . '">
                ';
                $xml = $xml . '<cfdi:Traslados>
                ';
                $xml = $xml . '<cfdi:Traslado Impuesto="002" TasaOCuota="0.160000" Importe="' . number_format(round($datos["iva_t"],2),2) . '" TipoFactor="Tasa"></cfdi:Traslado>
                ';
                if ($datos["ieps_t"] != "0") {
                    for ($i = 0; $i < count($importeieps); $i++) {
                        $xml = $xml . '<cfdi:Traslado Impuesto="003" Importe="' . number_format(round($datos["ieps_t"],2),2) . '"></cfdi:Traslado>
                ';
                    }
                }
                $xml = $xml . '</cfdi:Traslados>';

            $xml = $xml . '</cfdi:Impuestos>';
        }else{
            $xml = $xml . '<cfdi:Impuestos/>';
        }
        $xml = $xml . '<cfdi:Complemento></cfdi:Complemento>';
        $xml = $xml . '</cfdi:Comprobante>';

        return $xml;
    }
}
?>