<?php

namespace App\Lib;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\FielRequestBuilder\Fiel;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\FielRequestBuilder\FielRequestBuilder;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\RequestBuilderInterface;
use PhpCfdi\SatWsDescargaMasiva\Service;
use PhpCfdi\SatWsDescargaMasiva\WebClient\GuzzleWebClient;
use PhpCfdi\SatWsDescargaMasiva\Shared\ServiceEndpoints;
use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTimePeriod;
use PhpCfdi\SatWsDescargaMasiva\Shared\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RequestType;

use Illuminate\Support\Facades\DB;

class LibSat {

    function login( $id_empresa, $password ) {
        $objEmpresa = DB::table("gen_cat_empresa")->select("certificado","key")
        ->where("id_empresa",$id_empresa)
        ->first();
        if( $objEmpresa ) {
            try{
                $certificado = storage_path('empresa')."/credenciales/CER_EMPRESA_ID_".$id_empresa.'.cer';
                $key = storage_path('empresa')."/credenciales/KEY_EMPRESA_ID_".$id_empresa.'.key';
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

                // creación del objeto encargado de crear las solicitudes firmadas usando una FIEL
                $requestBuilder = new FielRequestBuilder($fiel);

                // Creación del servicio
                return [ "ok"=> true, "data" => new Service($requestBuilder, $webClient) ];

            } catch ( Throweable $e ) {
                return ["ok" => false, "message" => "Ha ocurrido un error al momento de identificarse"];
            }
        }
        return ["ok" => false, "message" => "La empresa no ha sido encontrada"];
    }

    function crearSolcitud($datos){
        $validar_service = $this->login($datos["id_empresa"],$datos["password"]);

        if(!$validar_service["ok"]){
            return $validar_service;
        }

        $service = $validar_service["data"];

        try {
            if($datos["emitidos"]){
                $request = QueryParameters::create(
                    DateTimePeriod::createFromValues($datos["fecha_inicial"], $datos["fecha_final"]),
                    DownloadType::issued(),
                    RequestType::cfdi(),
                    $datos["rfc"]
                );
            }
            if($datos["recibidos"]){
                $request = QueryParameters::create(
                    DateTimePeriod::createFromValues($datos["fecha_inicial"], $datos["fecha_final"]),
                    DownloadType::received(),
                    RequestType::cfdi(),
                    $datos["rfc"]
                );
            }
            // presentar la consulta
            $query = $service->query($request);
        }
        catch ( Throweable $e ) {
            return ["ok" => false, "message" => "Ha ocurrido un error al momento de crear la consulta"];
        }

        // verificar que el proceso de consulta fue correcto
        if (! $query->getStatus()->isAccepted()) {
            return [ "ok" => false, "message" => "Fallo al presentar la consulta: {$query->getStatus()->getMessage()}" ];
        }

        return [ "ok" => true, "data" => $query ];
    }

    function verificar($datos){
        //Tipo = 1 Para consultar el status de la solicitud
        $validar_service = $this->login($datos["id_empresa"],$datos["password"]);

        if(!$validar_service["ok"]){
            return $validar_service;
        }

        $service = $validar_service["data"];
        $requestId = $datos["id_solicitud"];
        try{
            // consultar el servicio de verificación
            $verify = $service->verify($requestId);
        }
        catch ( Throweable $e ) {
            return ["ok" => false, "message" => "Ha ocurrido un error al momento de consultar el estado de la consulta"];
        }

        //Validaciones
        // revisar que el proceso de verificación fue correcto
        if (! $verify->getStatus()->isAccepted()) {
            return [ "ok" => false, "message" => "Fallo al verificar la consulta {$requestId}: {$verify->getStatus()->getMessage()}" ];
        }

        // revisar que la consulta no haya sido rechazada
        if (! $verify->getCodeRequest()->isAccepted()) {
            return [ "ok" => false, "message" => "La solicitud {$requestId} fue rechazada: {$verify->getCodeRequest()->getMessage()}" ];
        }

        // revisar el progreso de la generación de los paquetes
        $statusRequest = $verify->getStatusRequest();

        if ($statusRequest->isExpired() || $statusRequest->isFailure() || $statusRequest->isRejected()) {
            return [ "ok" => false, "message" => "La solicitud {$requestId} no se puede completar" ];
        }
        if ($statusRequest->isInProgress() || $statusRequest->isAccepted()) {
            return [ "ok" => false, "message" => "La solicitud {$requestId} se está procesando" ];
        }
        if ($statusRequest->isFinished()) {
            return [ "ok" => true, "data" => $verify->getPackagesIds() ];
        }
    }

    function descargar($datos){
        $validar_service = $this->login($datos["id_empresa"],$datos["password"]);

        if(!$validar_service["ok"]){
            return $validar_service;
        }

        $service = $validar_service["data"];
        $packagesIds = $datos["archivos"];

        // consultar el servicio de verificación
        foreach($packagesIds as $packageId) {
            $download = $service->download($packageId);
            if (! $download->getStatus()->isAccepted()) {
                array_push($errores, "El paquete {$packageId} no se ha podido descargar: {$download->getStatus()->getMessage()}");
                continue;
            }
            $path = 'temp_file.zip';
            $contents = $download->getPackageContent();
            file_put_contents($path, $contents);
            $headers = array(
                'Content-Type: application/octet-stream',
                'Content-Disposition: attachment; filename=factura.xml'
            );
            return response()->download($path, 'cfdis.zip',$headers);
        }
        return [ "ok" => true, "data" => $ids, "errores" => $errores ];

    }
}

?>
