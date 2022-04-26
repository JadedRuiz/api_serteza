<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CapturaExport;
use App\Exports\EmpleadoExport;
use App\Exports\ContratoExport;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;


class ExcelController extends Controller
{
    public function formatoCapturaConceptos($empresa)
    {
        return Excel::download((new CapturaExport)->setearEmpresa($empresa), 'Precaptura de nómina.xlsx');
    }
    public function formatoEmpleados($empresa, $id_nomina)
    {
        return Excel::download((new EmpleadoExport)->setearDatos($empresa,$id_nomina), 'Formato de movimientos.xlsx');
    }
    public function formatoAltaEmpleados(Request $res)
    {
        $contrato_obj = new ContratoExport();
        $validar = $contrato_obj->obtenerFormatoAlta($res["id_cliente"]);
        if($validar){
            return $this->crearRespuesta(1,$validar["data"],200);
        }
        return $this->crearRespuesta(1,$validar["message"],301);
    }
}
