<?php

namespace App\Exports;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;

class ContratoExport
{
    public function obtenerContrato($id_puesto)
    {
        if($id_puesto == -1){
            return $this->contratoGenerico();
        }
    }

    public function contratoGenerico()
    {
        try{
            $file = storage_path('contratos');
            $phpword = new TemplateProcessor($file."\prueba.docx");
            $tags = $phpword->getVariables();
            $phpword->setValue('candidato','Jaded Enrique Ruiz Pech');
            $phpword->saveAs($file."\pruebaEdit.docx");
            return "echo";
        }catch(\PhpOffice\PhpWord\Exception\Exception $e){
            return ["ok" => false, "message" => $e->getCode()];
        }
        
    }
}