<?php
namespace App\Lib;

use App\Models\Empresa;
use App\Lib\Sello;
use nusoap_client;

class Timbrado {
    private $user;
    private $key;

    public function timbrar($datos)
    {
        $id_empresa = 1;
       //Recuperar datos empresa
       $datos_empresa = Empresa::select("no_certificado","certificado","key")
       ->where("id_empresa",$id_empresa)
       ->first();
       //Validar existencia de empresa
       if(!$datos_empresa){
        return $this->crearRespuesta(2,"No se ha podido recuperar la información de la empresa emisora",200);
       }

       //Variables globales del método
       $key = $datos_empresa->key;                                  //.Key de la empresa emisora
       $cer = $datos_empresa->certificado;                          //.Cer de la empresa emisora
       $numcer = $datos_empresa->no_certificado;                    //No_cer de la empresa emisora
       $user = env("USER_NAME");                                    //Usuario de acceso del proveedor de timbres
       $key  = env("USER_PASS");                                    //Llave de acceso del proveedor de timbres
       $gIDUsu = 1;                                                 //idusuario global.

       //Crear la conexión SOAP
       try{
           $url = env("URL_PROVEEDOR");
           $client = new nusoap_client($url, 'soap');
           $client->soap_defencoding = "UTF-8";
           $client->decode_utf8 = false;
       }catch(Throwable $e){
           return $this->crearRespuesta(2,"Error de conexion al proveedor : ".$client->getError(),200);
       }
       
       //Sellar
       $data = [
           "datos" => $datos,
           "credenciales" => [
               "key" => $key,
               "cer" => $cer,
               "no_cer" => $numcer
           ]
        ];
       $sello = new Sello();
       $resultado = $sello->sellar($data);
       return $resultado;
    }
}
?>