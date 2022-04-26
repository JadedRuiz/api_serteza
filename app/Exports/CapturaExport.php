<?php 
namespace App\Exports;

use App\Invoice;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CapturaExport implements FromView, ShouldAutoSize, WithStyles
{
    private $empresa;
 
    public function setearEmpresa(int $empresa = null)
    {
        $this->empresa = $empresa;
        return $this;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
            2    => ['font' => ['bold' => true]],
        ];
    }

    public function view(): View
    {
        try{
            $id_empresa = $this->empresa;
            $conceptos = DB::table('nom_conceptos')
            ->select("concepto","id_concepto")
            ->where("id_empresa",$id_empresa)
            ->get();
            $recuperar_id_clientes = DB::table('liga_empresa_cliente')
            ->select("id_cliente")
            ->where("id_empresa",$id_empresa)
            ->get();
            if(count($recuperar_id_clientes)>0 && count($conceptos)>0){
                $id_clientes = [];
                foreach($recuperar_id_clientes as $id_cliente){
                    array_push($id_clientes,$id_cliente->id_cliente);
                }
                $empleados = DB::table('nom_empleados as ne')
                ->select("ne.id_empleado", DB::raw('CONCAT(rcc.apellido_paterno, " ", rcc.apellido_materno, " ", rcc.nombre) as nombre'))
                ->join("rh_cat_candidato as rcc","rcc.id_candidato","=","ne.id_candidato")
                ->whereIn("rcc.id_cliente",$id_clientes)
                ->get();
                if(count($empleados)>0){
                    return view('prueba', compact('empleados','conceptos'));
                }else{
                    return $this->crearRespuesta(2,"No se han encontrado candidatos",200);
                }
            }else{
                return $this->crearRespuesta(2,"Está empresa no cuenta con clientes configurados",301);
            }
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
}