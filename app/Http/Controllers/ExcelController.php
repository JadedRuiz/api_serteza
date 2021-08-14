<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CapturaExport;

class ExcelController extends Controller
{
    public function formatoCapturaConceptos($empresa)
    {
        return Excel::download((new CapturaExport)->setearEmpresa($empresa), 'Precaptura de nómina.xlsx');
    }
}
