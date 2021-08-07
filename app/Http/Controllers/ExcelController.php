<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PruebaExport;

class ExcelController extends Controller
{
    public function test()
    {
        return Excel::download(new PruebaExport, 'invoices.xlsx');
    }
}
