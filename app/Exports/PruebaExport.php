<?php 
namespace App\Exports;

use App\Invoice;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PruebaExport implements FromView
{
    public function view(): View
    {
        return view('prueba', [
            "prueba" => "JAJA"
        ]);
    }
}