<?php

namespace App\Lib;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\FielRequestBuilder\Fiel;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\FielRequestBuilder\FielRequestBuilder;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\RequestBuilderInterface;
use PhpCfdi\SatWsDescargaMasiva\Service;
use PhpCfdi\SatWsDescargaMasiva\WebClient\GuzzleWebClient;
use PhpCfdi\SatWsDescargaMasiva\Shared\ServiceEndpoints;

use Illuminate\Support\Facades\DB;

class Autentificacion {
    
    function identificarse( $id_empresa, $password, $tipo ) {
        $objEmpresa = DB::table("gen_cat_empresa")->select("certificado","key")
        ->where("id_empresa",$id_empresa)
        ->first();

        if( $objEmpresa ) {
            try{
                $certificado = storage_path('empresa').$objEmpresa->certificado;
                $key = storage_path('empresa').$objEmpresa->key;
                $fiel = Fiel::create(
                    file_get_contents($certificado),
                    file_get_contents($key),
                    $password
                );

                // verificar que la FIEL sea válida (no sea CSD y sea vigente acorde a la fecha del sistema)
                if (! $fiel->isValid()) {
                    return ["ok" => false, "message" => "Las credenciales no son validas"];
                }

                // creación del web client basado en Guzzle que implementa WebClientInterface
                // para usarlo necesitas instalar guzzlehttp/guzzle pues no es una dependencia directa
                $webClient = new GuzzleWebClient();

                

                // Retorna el servicio
                //Tipo == 1 --> Ingresos, Egresos, Traslados, Nóminas y pagos
                //Tipo == 2 --> Retenciones
                if($tipo == 1){
                    // creación del objeto encargado de crear las solicitudes firmadas usando una FIEL
                    $requestBuilder = new FielRequestBuilder($fiel);
                    return new Service($requestBuilder, $webClient);
                }
                
                // creación del objeto encargado de crear las solicitudes firmadas usando una FIEL
                $requestBuilder = new RequestBuilderInterface($fiel);
                return new Service($requestBuilder, $webClient, null, ServiceEndpoints::retenciones());

            } catch ( Throweable $e ) {
                return ["ok" => false, "message" => "Ha ocurrido un error al momento de identificarse"];
            }
            
        }
        return ["ok" => false, "message" => "La empresa no ha sido encontrada"];
    }
}

?>