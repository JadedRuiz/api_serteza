<?php

namespace App\Lib;
use App\Lib\Sello;
use nusoap_client;
use DOMDocument;

class Timbrado {
    private $user;
    private $key;

    public function timbrar($datos)
    {
       //Variables globales del método
       $user = env("USER_NAME");                                    //Usuario de acceso del proveedor de timbres
       $key  = env("USER_PASS");                                    //Llave de acceso del proveedor de timbres
       $gIDUsu = 1;                                                 //idusuario global.

       //Crear la conexión SOAP
       try{
		   //Se definen las empresas que timbraran en 4.0
            $url = env("URL_TIMBRE40");
            $client = new nusoap_client($url, 'soap');
            $client->soap_defencoding = "UTF-8";
            $client->decode_utf8 = false;
       }catch(Throwable $e){
           return ["ok" => false, "message" => "Error de conexion al proveedor : ".$client->getError()];
       }
       $mynamespace = "http://mycommerce.mx";

       //Sellar
       $sello = new Sello();
       $resultado = $sello->sellar($datos);
       if($resultado["ok"]){
            try{
                $myDom = new DOMDocument();
                $myDom->loadXML($resultado["data"]);
            }catch(Throwable $e){
                return ["ok" => false, "message" => "Error al recuperar XML : ".$e->getMessage()];
            }
            $xml = trim($myDom->saveXML());
            //obtenerTimbrado
            $params = array(
                'CFDIcliente' => $xml,
                'Usuario' => $user,
                'password' => $key,
            );
            $res_client = $client->call('obtenerTimbrado', $params);
            if($client->fault){
                return ["ok" => false, "message" => "Error al consumir el servicio del provedor"];
            }
            // return ["ok" => false, "message" => json_encode($res_client)];
            //Se consume el método
            $xmlobtenerTimbrado = $res_client['obtenerTimbradoResult'];
            $xmlTimbre = $xmlobtenerTimbrado['timbre'];
            if(isset($xmlTimbre['errores']) && $xmlTimbre['!esValido'] != "True"){
                $arreglo_erorres = [];
                foreach($xmlTimbre['errores']["Error"] as $error){
                    array_push($arreglo_erorres,[$error["!codigo"] . ": ".$error["!mensaje"]]);
                }
                return ["ok" => false, "message" => $arreglo_erorres];
            }
            
            $xmlTimbreFiscalDigital = $xmlTimbre['TimbreFiscalDigital'];
            $xsi = $xmlTimbreFiscalDigital['!xsi:schemaLocation'];
            $version = $xmlTimbreFiscalDigital['!Version'];
            $FechaTimbrado = $xmlTimbreFiscalDigital['!FechaTimbrado'];
            $selloCFD = $xmlTimbreFiscalDigital['!SelloCFD'];
            $noCertificadoSAT = $xmlTimbreFiscalDigital['!NoCertificadoSAT'];
            $selloSAT = $xmlTimbreFiscalDigital['!SelloSAT'];
            $UUID = $xmlTimbreFiscalDigital['!UUID'];
            $provsert = $xmlTimbreFiscalDigital['!RfcProvCertif'];

            $dat = 'http://www.sat.gob.mx/TimbreFiscalDigital';

            $doc = new DOMDocument();
            $doc->loadXML($xml);
            
            $c = $doc->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/4', 'Complemento')->item(0);
            
            
            $nodo = $doc->createElement("tfd:TimbreFiscalDigital");
            $nuevo_nodo = $c->appendChild($nodo);

            $nuevo_nodo->setAttribute('xmlns:tfd', $dat);
            $nuevo_nodo->setAttribute('xsi:schemaLocation', 'http://www.sat.gob.mx/TimbreFiscalDigital http://www.sat.gob.mx/sitio_internet/TimbreFiscalDigital/TimbreFiscalDigital.xsd');
            $nuevo_nodo->setAttribute('Version', $version);
            $nuevo_nodo->setAttribute('FechaTimbrado', $FechaTimbrado);
            $nuevo_nodo->setAttribute('RfcProvCertif', $provsert);
            $nuevo_nodo->setAttribute('SelloCFD', $selloCFD);
            $nuevo_nodo->setAttribute('NoCertificadoSAT', $noCertificadoSAT);
            $nuevo_nodo->setAttribute('SelloSAT', $selloSAT);
            $nuevo_nodo->setAttribute('UUID', $UUID);
            $xmlsello = $doc->saveXML();
            
            return ["ok" => true, "data" => $xmlsello];
       }
		return ["ok" => false, "message" => $resultado["message"]];
    }
}
?>