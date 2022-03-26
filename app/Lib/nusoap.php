<?php

///////
////////ESTE ES EL FORMATO PARA GENERAR EL XML
//////

error_reporting(0);
class sello
{
    public $rutallave = "";
    public $rutacertificado = "";
    public $dat = "";
    public $numerocer = "";
    public static function sellar($datos)
    {
        $dat = $datos;

        $llaves = explode("|", $datos);
        // for ($i=0; $i < count($llaves); $i++) {
        //   echo $i." es su indice".$llaves[$i]."<br>";
        // }

        $conexion = new conexion();
        $resp = $conexion->ejecutarconsulta("SELECT * FROM gen_catempresas WHERE EmpresaID = $llaves[57]");
        foreach ($resp as $d) {
            $key = $d['KeyCertificado'];
            $cer = $d['Certificado'];
            $numcer = $d['NoCertificado'];
        }

        $private = openssl_pkey_get_private(file_get_contents($key));
        $certificado = str_replace(array('\n', '\r'), '', base64_encode(file_get_contents($cer)));

        $pruebaxml = new gen_xml();

        $cfdi = $pruebaxml->gen_doc($dat);
        //$cfdi='cachktest_0.xml';

        $xdoc = new DomDocument();
        $xdoc->loadXML($cfdi) or die("XML invalido");

        $XSL = new DOMDocument();
        $XSL->load('../utilerias/cadenaoriginal_3_2.xslt');

        $proc = new XSLTProcessor;
        $proc->importStyleSheet($XSL);

        $cadena_original = $proc->transformToXML($xdoc);
        $datos = explode("|", $cadena_original);
        $cadena_original = "";

        for ($i = 1; $i < count($datos); $i++) {
            $cadena_original = $cadena_original . "|" . trim($datos[$i]);
        }
        openssl_sign($cadena_original, $sig, $private);
        $sello = base64_encode($sig);

        $c = $xdoc->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Comprobante')->item(0);
        $c->setAttribute('sello', $sello);
        $c->setAttribute('certificado', $certificado);
        $c->setAttribute('noCertificado', $numcer);
        $xmlsello = $xdoc->saveXML();
        file_put_contents($numcer . ".xml", $xmlsello);
        return $xmlsello;
    }
} ///////////////////////////////////////////////////////////////////////////////////////////

class gen_xml
{
    public $dat;
    // public static function gen_xml(){

    // }

    public static function gen_doc($dat)
    {

        $datos = explode("|", $dat);

        $serie = $datos[12];
        $folio = $datos[0];
        $fecha = str_replace(" ", "T", $datos[1]);
        $tipocomprobante = $datos[10];
        $formapago = $datos[2];
        $metodopago = $datos[13];
        $condicionpago = $datos[11];
        $numerocer = ""; //pendiente
        $subtotal = $datos[3];
        if ($datos[4] == "X") {
            $datos[4] = 0;
        }
        $descuento = $datos[4];
        if ($datos[5] != "X" && $datos[6] == "X") {
            $totalimptrasladado = $datos[5];
        } elseif ($datos[5] == "X" && $datos[6] != "X") {
            $totalimptrasladado = $datos[6];
        } else {
            $totalimptrasladado = $datos[5] + $datos[6];
        }
        $importeotros = $datos[7];
        $impuestoiva = "IVA";
        $tasaiva = 16;
        $importeiva = $datos[5];
        $impuestoieps = $datos[6];
        $tasaieps = explode(",", $datos[45]);
        $importeieps = explode(",", $datos[46]);
        $tasaotrosimp = explode(",", $datos[47]);
        $importeotrosimp = explode(",", $datos[48]);
        $codigopro = explode(",", $datos[49]);
        $moneda = "MXM"; //$datos[8];
        //$tipocambio==$datos[9];
        $total = $datos[8];
        $ine = $datos[9];
        $lugarexpedicion = $datos[24] . "," . $datos[23];
        $certificado = ""; //pendiente
        $sello = ""; //sacar
        $rfcemi = $datos[16];
        $curpemi = $datos[17];
        $nombreemi = $datos[15];
        $regimenemi = $datos[14];
        $calleemi = $datos[18];
        $referenciaemi = $datos[26];
        if ($datos[20] == "X") {$numextemi = "S/N";} else { $numextemi = $datos[20];}
        if ($datos[19] == "X") {$numintemi = "N/A";} else { $numintemi = $datos[19];}
        $coloniaemi = $datos[25];
        $municipioemi = $datos[24];
        $estadoemi = $datos[23];
        $paisemi = "MEXICO"; //$datos[22];
        $codigopostalemi = $datos[21];
        $calleexp = $datos[18];
        $referenciaexp = $datos[26];
        if ($datos[20] == "X") {$numextexp = "S/N";} else { $numextexp = $datos[20];}
        if ($datos[19] == "X") {$numintexp = "N/A";} else { $numintexp = $datos[19];}
        $numintexp = $datos[19];
        $coloniaexp = $datos[25];
        $municipioexp = $datos[24];
        $estadoexp = $datos[23];
        $paisexp = "MEXICO"; //$datos[22];
        $codigopostalexp = $datos[21];
        $rfcrec = $datos[29];
        $nombrerec = $datos[27];
        $callerec = $datos[31];
        if ($datos[33] == "X") {$numextrec = "S/N";} else { $numextrec = $datos[20];}
        if ($datos[32] == "X") {$numintrec = "N/A";} else { $numintrec = $datos[19];}

        $codigopostalrec = $datos[34];
        $paisrec = "MEXICO"; //$datos[35];
        $estadorec = trim($datos[36]);
        $municipiorec = $datos[37];
        $coloniarec = $datos[38];
        $referenciarec = $datos[39];
        //////////////////Conceptos////////////////////////////
        $cantidad = explode(",", $datos[40]);
        $unidad = explode(",", $datos[43]);
        $descripcion = explode(",", $datos[41]);
        $valoruni = explode(",", $datos[44]);
        $importe = explode(",", $datos[42]);

        /////////////////////Complemento ine///////////////////
        $tipoproceso = $datos[50];
        $tipocomite = $datos[51];
        $idcontabilidad = $datos[52];

        //////////////////////Detalle INE///////////////////

        $claveentidad = explode(",", $datos[53]);
        $ambito = explode(",", $datos[54]);

        /////////////////////ID contabilidad INE////////////////

        $idcontabilidaddet = explode(",", $datos[55]);

        $xml = "";
        //motivoDescuento="PREGUNTAR"

        $xml = $xml . '<?xml version="1.0" encoding="UTF-8"?>';
        $xml = $xml . '<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/3" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv32.xsd http://www.sat.gob.mx/ine http://www.sat.gob.mx/sitio_internet/cfd/ine/ine10.xsd" LugarExpedicion="' . $lugarexpedicion . '" metodoDePago="' . $metodopago . '" condicionesDePago="' . $condicionpago . '" tipoDeComprobante="' . $tipocomprobante . '" total="' . $total . '" descuento="' . $descuento . '" subTotal="' . $subtotal . '" certificado="" formaDePago="' . $formapago . '" sello="" noCertificado="" fecha="' . $fecha . '" Moneda="' . $moneda . '" folio="' . $folio . '" serie="' . $serie . '" version="3.2" >
  		<cfdi:Emisor nombre="' . $nombreemi . '" rfc="' . $rfcemi . '">
  		';

        $xml = $xml . '<cfdi:DomicilioFiscal calle="' . $calleemi . '" referencia="' . $referenciaemi . '" noExterior="' . $numextemi . '" noInterior="' . $numintemi . '" colonia="' . $coloniaemi . '" municipio="' . $municipioemi . '" estado="' . $estadoemi . '" pais="' . $paisemi . '" codigoPostal="' . $codigopostalemi . '" />
		';
        $xml = $xml . '<cfdi:ExpedidoEn calle="' . $calleexp . '" referencia="' . $referenciaexp . '" noExterior="' . $numextexp . '" noInterior="' . $numintexp . '" colonia="' . $coloniaexp . '" municipio="' . $municipioexp . '" estado="' . $estadoexp . '" pais="' . $paisexp . '" codigoPostal="' . $codigopostalexp . '" />
		';
        $xml = $xml . '<cfdi:RegimenFiscal Regimen="' . $regimenemi . '" />
		';
        $xml = $xml . '</cfdi:Emisor>
		';
        $xml = $xml . '<cfdi:Receptor nombre="' . $nombrerec . '" rfc="' . $rfcrec . '">
		';
        $xml = $xml . '<cfdi:Domicilio calle="' . $callerec . '" referencia="' . $referenciarec . '" noExterior="' . $numextrec . '" noInterior="' . $numintrec . '" colonia="' . $coloniarec . '" municipio="' . $municipiorec . '" estado="' . $estadorec . '" pais="' . $paisrec . '" codigoPostal="' . $codigopostalrec . '" />
		';
        $xml = $xml . '</cfdi:Receptor>
		';
        $xml = $xml . '<cfdi:Conceptos>
		';
        for ($i = 0; $i < count($cantidad); $i++) {
            $xml = $xml . '<cfdi:Concepto importe="' . $importe[$i] . '" noIdentificacion="' . $codigopro[$i] . '" valorUnitario="' . $valoruni[$i] . '" descripcion="' . $descripcion[$i] . '" unidad="' . $unidad[$i] . '" cantidad="' . $cantidad[$i] . '" />
			';
        }
        $xml = $xml . '</cfdi:Conceptos>
		';
        if ($importeiva != "X") {

            $xml = $xml . '<cfdi:Impuestos totalImpuestosTrasladados="' . $totalimptrasladado . '">
			';
            $xml = $xml . '<cfdi:Traslados>
			';
            $xml = $xml . '<cfdi:Traslado impuesto="IVA" tasa="16.00" importe="' . $importeiva . '"></cfdi:Traslado>
			';
            if ($impuestoieps != "X") {
                for ($i = 0; $i < count($importeieps); $i++) {
                    $xml = $xml . '<cfdi:Traslado impuesto="IEPS" tasa="' . $tasaieps[$i] . '" importe="' . $importeieps[$i] . '"></cfdi:Traslado>
			';
                }
            }
            $xml = $xml . '</cfdi:Traslados>
			';
            $xml = $xml . '</cfdi:Impuestos>
			';
        } else {
            $xml = $xml . '<cfdi:Impuestos/>
			';
        }

        $xml = $xml . '<cfdi:Complemento>
		';
        if ($ine == 1 || $importeotros > 0) {
            if ($ine == 1) {
                $xml = $xml . '<ine:INE Version="1.1" TipoProceso="' . $tipoproceso . '"';if ($tipocomite != "X") {$xml = $xml . ' TipoComite="' . $tipocomite . '"';}if ($idcontabilidad != "X") {$xml = $xml . ' IdContabilidad="' . $idcontabilidad . '"';}$xml = $xml . ' xmlns:ine="http://www.sat.gob.mx/ine">
		';
                if ($claveentidad[0] != "X") {
                    for ($i = 0; $i < count($claveentidad); $i++) {
                        $xml = $xml . '<ine:Entidad ClaveEntidad="' . $claveentidad[$i] . '"';if ($ambito[$i] != "") {$xml = $xml . ' Ambito="' . $ambito[$i] . '"';}$xml = $xml . '>
           ';
                        if ($idcontabilidaddet != "X") {
                            $xml = $xml . '<ine:Contabilidad IdContabilidad="' . $idcontabilidaddet[$i] . '"/>
           ';
                        }
                        $xml = $xml . '</ine:Entidad>
           ';
                    }
                }

                $xml = $xml . '</ine:INE>
       ';
            }
            if ($importeotros > 0) {

                $xml = $xml . '<implocal:ImpuestosLocales xmlns:implocal="http://www.sat.gob.mx/implocal" version="1.0" TotaldeRetenciones="0" TotaldeTraslados="' . $importeotros . '">
    ';
                for ($i = 0; $i < count($tasaotrosimp); $i++) {
                    $xml = $xml . '<implocal:TrasladosLocales ImpLocTrasladado="ISSVFBCA" TasadeTraslado="' . $tasaotrosimp[$i] . '" Importe="' . $importeotrosimp[$i] . '"></implocal:TrasladosLocales>
      ';
                }

                $xml = $xml . '</implocal:ImpuestosLocales>
  ';

            }
        }
        $xml = $xml . '</cfdi:Complemento>
  ';

        $xml = $xml . '</cfdi:Comprobante>
';

        $doc = DOMDocument::loadXML($xml);
        $doc->saveXML();
// $doc = new DOMDocument();
// $doc->loadXML($xml);
// echo $doc->saveXML();
        $doc2 = DOMDocument::loadXML($xml);
//echo $doc2->save("sellar.xml");
        return $xml;

    }

}
/*
$xml=$xml.'<?xml version="1.0" encoding="UTF-8"?>';
$xml=$xml.'<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/3" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv32.xsd http://www.sat.gob.mx/nomina12 http://www.sat.gob.mx/sitio_internet/cfd/nomina/nomina12.xsd" LugarExpedicion="'.$lugarexpedicion.'" metodoDePago="'.$metodopago.'" tipoDeComprobante="'.$tipocomprobante.'" total="'.$total.'" descuento="'.$descuento.'" subTotal="'.$subtotal.'" certificado="" formaDePago="'.$formapago.'" sello="" noCertificado="" fecha="'.$fecha.'" Moneda="'.$moneda.'" folio="'.$folio.'" serie="'.$serie.'" version="3.2" >
<cfdi:Emisor nombre="'.$nombreemi.'" rfc="'."GOYA780416GM0".'">
';
$xml=$xml.'<cfdi:RegimenFiscal Regimen="'."612".'" />
';
$xml=$xml.'</cfdi:Emisor>
';
$xml=$xml.'<cfdi:Receptor nombre="'.$nombrerec.'" rfc="'."AAQM610917QJA".'">
';
$xml=$xml.'</cfdi:Receptor>
';
$xml=$xml.'<cfdi:Conceptos>
';
$xml=$xml.'<cfdi:Concepto importe="'.$importe.'" valorUnitario="'.$valoruni.'" descripcion="'.$descripcion.'" unidad="'.$unidad.'" cantidad="'.$cantidad.'" />
';
$xml=$xml.'</cfdi:Conceptos>
';
$xml=$xml.'<cfdi:Impuestos />
';
$xml=$xml.'<cfdi:Complemento>
';
$xml=$xml.'<nomina12:Nomina Version="1.2" FechaPago="'.$fechapago.'" FechaInicialPago="'.$fechainipago.'" FechaFinalPago="'.$fechafinpago.'" NumDiasPagados="'.$diaspagados.'" TipoNomina="'.$tiponomina;if($totaldeducciones!="X"){$xml=$xml.'" TotalDeducciones="'.$totaldeducciones;}if($totalotros!="X"){$xml=$xml.'" TotalOtrosPagos="'.$totalotros;}if($totalpercepciones!="X"){$xml=$xml.'" TotalPercepciones="'.$totalpercepciones;}
$xml=$xml.'" xmlns:nomina12="http://www.sat.gob.mx/nomina12">
';
$xml=$xml.'<nomina12:Emisor';if($registropat!="X"){$xml=$xml.' RegistroPatronal="'."5689846157".'"';} if($rfcpat!="X"){$xml=$xml.' Curp="SASE880422HMCNRD00"';}$xml=$xml. '>
';
if($origenrecurso!="X")
{
$xml=$xml.'<nomina12:EntidadSNCF OrigenRecurso="'.$origenrecurso.'"/>
';
}
$xml=$xml.'</nomina12:Emisor>
';
$xml=$xml.'<nomina12:Receptor Curp="'."SASE880422HMCNRD00".'" TipoContrato="'.$tipocontrato.'" TipoRegimen="'.$tiporegimen.'" NumEmpleado="'.$numeroempleado.'" PeriodicidadPago="'.$periodicidadpago.'" ClaveEntFed="'.$claveentidad;if($numeroseguro!="X"){$xml=$xml.'" NumSeguridadSocial="'.$numeroseguro;}if($banco!="X"){$xml=$xml.'" Banco="'.$banco.'" CuentaBancaria="'.$ctabancaria;}if($fechainilab!="X"){$xml=$xml.'" FechaInicioRelLaboral="'.$fechainilab;}if($antiguedad!="X"){$xml=$xml.'" Antigüedad="'.$antiguedad;}if($puesto!="X"){$xml=$xml.'" Puesto="'.$puesto;}if($salariobase!="X"){$xml=$xml.'"  SalarioBaseCotApor="'.$salariobase;}if($riegopuesto!="X"){$xml=$xml.'" RiesgoPuesto="'.$riegopuesto;}if($saliariodiaintegrado!="X"){$xml=$xml.'" SalarioDiarioIntegrado="'.$saliariodiaintegrado;}if($tipojornada!="X"){$xml=$xml.'" TipoJornada="'.$tipojornada;}$xml=$xml.'">
';
if($rfclab!="X"){
$xml=$xml.'<nomina12:SubContratacion RfcLabora="'.$rfclab.'" PorcentajeTiempo="'.$porcentajelab.'"/>
';
}
$xml=$xml.'</nomina12:Receptor>
';

$xml=$xml.'<nomina12:Percepciones TotalExento="'.$totalex.'" TotalGravado="'.$totgrav;if($totinde!="X"){$xml=$xml.'" TotalSeparacionIndemnizacion="'.$totinde;}if($totalex!="X"){$xml=$xml.'" TotalSueldos="'.$totgrav;}$xml=$xml.'">
';
for ($i=0; $i <count($claveper) ; $i++) {
$xml=$xml.'<nomina12:Percepcion TipoPercepcion="'.$tipopercepcion[$i].'" Clave="'.$claveper[$i].'" Concepto="'.$conceptoper[$i].'" ImporteGravado="'.$importegra[$i].'" ImporteExento="'.$importeexe[$i].'"/>
';
}

if($totalpagoinde!="X"){
$xml=$xml.'<nomina12:SeparacionIndemnizacion TotalPagado="'.$totalpagoinde.'" NumAñosServicio="'.$añosservicios.'" UltimoSueldoMensOrd="'.$ultimosueldomes.'" IngresoAcumulable="'.$ingresoacuinde.'" IngresoNoAcumulable="'.$ingresonoacuinde.'"/>
';
}
if($diaextra[0]!="X"){
for ($i=0; $i <count($diaextra) ; $i++) {
$xml=$xml.'<nomina12:HorasExtra Dias="'.$diaextra[$i].'" TipoHoras="'.$tipoextra[$i].'" HorasExtra="'.$horasextras[$i].'" ImportePagado="'.$importepag[$i].'"/>
';
}
}

$xml=$xml.'</nomina12:Percepciones>
';
if($tipodeduccion[0]!="X"){
$xml=$xml.'<nomina12:Deducciones ';if($totalotrasde!="X"){$xml=$xml.'TotalOtrasDeducciones="'.$totalotrasde;}if($totalimpretenidos!="X"){$xml=$xml.'" TotalImpuestosRetenidos="'.$totalimpretenidos;}$xml=$xml.'">
';
for ($i=0; $i <count($clavedecuccion) ; $i++) {
$xml=$xml.'<nomina12:Deduccion TipoDeduccion="'.$tipodeduccion[$i].'" Clave="'.$clavedecuccion[$i].'" Concepto="'.$conceptodeduc[$i].'" Importe="'.$importededuccion[$i].'"/>
';
}

$xml=$xml.'</nomina12:Deducciones>
';
}

if(($tipootropago[0]!="X")||($subsidiocausado!="X")||($saldofavor!="X")){

$xml=$xml.'<nomina12:OtrosPagos>
';
if($tipootropago[0]!="X"){
for ($i=0; $i <count($importeotropago) ; $i++) {
$xml=$xml.'<nomina12:OtroPago TipoOtroPago="'.$tipootropago[$i].'" Clave="'.$claveotropago[$i].'" Concepto="'.$conceptootropago[$i].'" Importe="'.$importeotropago[$i].'"/>
';
}

}
if($subsidiocausado!="X"){
$xml=$xml.'<nomina12:SubsidioAlEmpleo SubsidioCausado="'.$subsidiocausado.'"/>
';
}
if($saldofavor!="X"){
$xml=$xml.'<nomina12:CompensacionSaldoAFavor SaldoAFavor="'.$saldofavor.'" Año="'.$año.'" RemanenteSalFav="'.$remanente.'"/>
';
}
$xml=$xml.'</nomina12:OtrosPagos>
';
}
if($diasinca[0]!="X"){
$xml=$xml.'<nomina12:Incapacidades>
';
for ($i=0; $i <count($diasinca) ; $i++) {
$xml=$xml.'<nomina12:Incapacidad DiasIncapacidad="'.$diasinca[$i].'" TipoIncapacidad="'.$tipoinca[$i];if($importemone!="X"){$xml=$xml.'" ImporteMonetario="'.$importemone[$i];}$xml=$xml.'"/>
';
}

$xml=$xml.'</nomina12:Incapacidades>
';
}
$xml=$xml.'</nomina12:Nomina>
';
$xml=$xml.'</cfdi:Complemento>
';
$xml=$xml.'</cfdi:Comprobante>
';*/
////////////////////////////////////////////////////////////////////////////////////////////
class conexion
{

    public $servidor = "65.99.225.252";
    public $usuario = "sertezac_omar";
    public $pass = "5erte34?crh";
    public $nombd = "sertezac_factura";

	// var $servidor="localhost";
	// var $usuario="root";
	// var $pass="";
	// var $nombd="sertezac_factura";

    public function conectar()
    {
        @$con = mysqli_connect($this->servidor, $this->usuario, $this->pass, $this->nombd);
        if (!$con) {
            //return "ERROR|AL CONECTAR LA BASE DE DATOS";
            exit();
        } else {
            return $con;
        }
    }
    public function ejecutarconsulta($consulta)
    {
        $resp = mysqli_query($this->conectar(), $consulta);
        return $resp;
    }

    public function insetartimbrado($consulta)
    {
        mysqli_query($this->conectar(), $consulta);
        $consulta = "SELECT MAX(TimbreID) AS id FROM tim_cattimbres";
        $resp = mysqli_query($this->conectar(), $consulta);
        foreach ($resp as $dat) {
            $datos = $dat['id'];
        }
        return $datos;
    }

}

//////////////////////////////////////////////////////////////////////////////////////////////

class timbrado
{

    public static function timbrar($dato, $tipoAccion)
    {
        $dat = $dato;
        $arreglodatos = explode("|", $dat);
        //echo $arreglodatos[57];
        $conexion = new conexion();
        $resp = $conexion->ejecutarconsulta("SELECT * FROM gen_catempresas WHERE EmpresaID = $arreglodatos[57]");
        foreach ($resp as $d) {
            $key = $d['KeyCertificado'];
            $cer = $d['Certificado'];
            $numcer = $d['NoCertificado'];
        }

        $ModoTimbrado = $tipoAccion; //T=test, P=produccion
        $gIDUsu = 1; //idusuario global.
        //$ModoTimbrado='T';
        if ($ModoTimbrado == 'T') {
            //TEST "Se puede agregar (?WSDL) si su sistema lo requiere.
            $client = new nusoap_client('https://stagetimbrado.facturador.com/timbrado.asmx?WSDL', 'soap');
            $client->soap_defencoding = "UTF-8";
            $client->decode_utf8 = false;
            $user = 'test';
            $pw = 'TEST';

        }
        if ($ModoTimbrado == 'P') {
            //PRODUCCION "Se puede agregar (?WSDL) si su sistema lo requiere.
            $client = new nusoap_client('https://timbrado.facturador.com/timbrado.asmx?WSDL', 'soap');
            $client->soap_defencoding = "UTF-8";
            $client->decode_utf8 = false;
            $user = 'GilKatzyn';
            $pw = 'ZpaneTx6';
        }
        //
        if ($sError = $client->getError()) {
            //echo "No se pudo realizar la operación [" . $sError . "]";
            die();
        }
        $mynamespace = 'http://mycommerce.mx';
        //EL XML
//  $_ArchivoXML='sellar1.xml'; //ingrese en raiz el xml para timbrar
        $xmlsello = new sello();
        $_ArchivoXML = $xmlsello->sellar($dat);
        //file_put_contents("archivosinsello.xml", $_ArchivoXML);

        $myDom = new DomDocument();
        $myDom->loadXML($_ArchivoXML) or die("XML invalido");

        //        $myDom = new DOMDocument();
        // $myDom->load($_ArchivoXML);

        $xml = trim($myDom->saveXML());

        //obtenerTimbrado
        $params = array(
            'CFDIcliente' => $xml,
            'Usuario' => $user,
            'password' => $pw,
        );
        $resultado = $client->call('obtenerTimbrado', $params);
        //

        //cancelarComprobante
        //$params = array(
        //    'xmlCancelacion' => $xml,
        //    'usuario' => $user,
        //    'password' => $pw
        //);
        //$resultado = $client->call('cancelarComprobante',$params,$mynamespace);
        //

        if ($client->fault) {
            //echo '<h2>Falla en timbrado</h2><pre>'; print_r($result); echo '</pre>';
        } else {
            //debug 1
            //echo "No hubo error: <br>";
            //echo "Resultado: <br>";
            //print_r($resultado);
            //echo "<br>"."Resultado regresado..."."<br>";
            //echo '<pre>'; print_r($resultado); '</pre><hr>';
            //echo "<br>"."Respuesta llama respuesta"."<br>";
            //echo '<pre>'.htmlspecialchars($client->response, ENT_QUOTES).'</pre>';

            $xmlobtenerTimbrado = $resultado['obtenerTimbradoResult'];
            $xmlTimbre = $xmlobtenerTimbrado['timbre'];
            @$Err0r = $xmlTimbre['errores'];
            @$men_error = $Err0r['Error'];
            $fatality = count($Err0r);
            if ($fatality == 0) {
                # code...
                //echo "<br>FATALITY: ".$fatality."<br>";
                $xmlTimbreFiscalDigital = $xmlTimbre['TimbreFiscalDigital'];
                $xsi = $xmlTimbreFiscalDigital['!xsi:schemaLocation'];
                $version = $xmlTimbreFiscalDigital['!version'];
                $FechaTimbrado = $xmlTimbreFiscalDigital['!FechaTimbrado'];
                $selloCFD = $xmlTimbreFiscalDigital['!selloCFD'];
                $noCertificadoSAT = $xmlTimbreFiscalDigital['!noCertificadoSAT'];
                $selloSAT = $xmlTimbreFiscalDigital['!selloSAT'];
                $UUID = $xmlTimbreFiscalDigital['!UUID'];

                //echo "<br>";

                $dat = 'http://www.sat.gob.mx/TimbreFiscalDigital';

                $doc = new DOMDocument();
                $doc->load($numcer . '.xml');
                $c = $doc->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Complemento')->item(0);
                $nodo = $doc->createElement("tfd:TimbreFiscalDigital");
                $nuevo_nodo = $c->appendChild($nodo);

                $nuevo_nodo->setAttribute('xmlns:tfd', $dat);
                $nuevo_nodo->setAttribute('xsi:schemaLocation', 'http://www.sat.gob.mx/TimbreFiscalDigital http://www.sat.gob.mx/sitio_internet/TimbreFiscalDigital/TimbreFiscalDigital.xsd');
                $nuevo_nodo->setAttribute('version', $version);
                $nuevo_nodo->setAttribute('FechaTimbrado', $FechaTimbrado);
                $nuevo_nodo->setAttribute('selloCFD', $selloCFD);
                $nuevo_nodo->setAttribute('noCertificadoSAT', $noCertificadoSAT);
                $nuevo_nodo->setAttribute('selloSAT', $selloSAT);
                $nuevo_nodo->setAttribute('UUID', $UUID);
                $xmlsello = $doc->saveXML();
                file_put_contents($numcer . ".xml", $xmlsello);

                $_ArchivoXML = $numcer . '.xml';
                $myDom = new DomDocument();
                $myDom->load($_ArchivoXML); // or die("XML invalido");
                $xmlCom = trim($myDom->saveXML());

                $xml2 = utf8_encode($xmlCom);

                $conexion = new conexion();
                $resp = $conexion->insetartimbrado("INSERT INTO tim_cattimbres (EmpresaID,ClienteID,CadenaOriginal,Xml,Fechacomprobante,NetoFactura,facturaID,Estatus,Fechatimbrado,SelloCFD,NocertificadoSAT,SelloSAT,UUID) VALUES ('$arreglodatos[57]','$arreglodatos[58]','$dato','$xml2','$arreglodatos[1]','$arreglodatos[8]','$arreglodatos[59]',1,'$FechaTimbrado','$selloCFD','$noCertificadoSAT','$selloSAT','$UUID') ");
                // echo "<br> correctos id=".$resp;

                $bien = trim($UUID, "\n");
                unlink($numcer . ".xml");
                return $bien . "|" . $resp;

            } else {

                $xml2 = utf8_encode($xml);
                $conexion = new conexion();

                $resp = $conexion->insetartimbrado("INSERT INTO tim_cattimbres (EmpresaID,ClienteID,CadenaOriginal,Xml,Fechacomprobante,NetoFactura,facturaID,Estatus,Fechatimbrado,SelloCFD,NocertificadoSAT,SelloSAT,UUID) VALUES ('$arreglodatos[57]','$arreglodatos[58]','$dato','$xml2','$arreglodatos[1]','$arreglodatos[8]','$arreglodatos[59]',0,'-','-','-','-','-') ");
//echo "<br> incorrectos id=".$resp;
                $cadenaerror = "";
                @$especial = $men_error[0];
                for ($i = 0; $i < count($men_error); $i++) {
                    if (count($especial) == 0) {
                        $cadenaerror = $cadenaerror . "|" . $men_error['!mensaje'];
                    } else {
                        $cadenaerror = $cadenaerror . "|" . $men_error[$i]['!mensaje'];
                    }
                }
                unlink($numcer . ".xml");

                return "ERROR" . $cadenaerror;

            }
        }

        //debug 2
//  echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
//  echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
//  echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
    }
}
