<?php 
namespace App\Exports;

use App\Invoice;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class EmpleadoExport implements FromView, ShouldAutoSize, WithStyles
{
    private $empresa;
    private $id_nomina;

    public function setearDatos(int $empresa = null, int $id_nomina = null)
    {
        $this->empresa = $empresa;
        $this->id_nomina = $id_nomina;
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
            $recuperar_id_clientes = DB::table('liga_empresa_cliente')
            ->select("id_cliente")
            ->where("id_empresa",$this->empresa)
            ->get();
            if(count($recuperar_id_clientes)>0){
                $id_clientes = [];
                foreach($recuperar_id_clientes as $id_cliente){
                    array_push($id_clientes,$id_cliente->id_cliente);
                }
            }else{
                return $this->crearRespuesta(2,"No se tiene cllientes configurados",301);
            }
            $datos_empresa = DB::table('gen_cat_empresa as gce')
            ->select("gce.empresa","gcf.nombre as url_foto")
            ->join("gen_cat_fotografia as gcf","gcf.id_fotografia","=","gce.id_fotografia")
            ->where("id_empresa",$this->empresa)
            ->first();
            $datos_empresa->url_foto = storage_path("empresa")."/".$datos_empresa->url_foto;
            $datos = DB::table('nom_empleados as ne')
            ->select("ne.id_empleado","gcc.cliente","ns.sucursal","ncn.nomina","rcc.apellido_paterno","rcc.apellido_materno","rcc.nombre","rcc.rfc","rcc.curp","rcc.numero_seguro","ne.fecha_ingreso","gcp.puesto","gcdd.departamento","ne.cuenta","ne.sueldo_diario","ne.sueldo_integrado","ne.sueldo_complemento","gcd.calle","gcd.numero_interior","gcd.numero_exterior","gcd.cruzamiento_uno","gcd.cruzamiento_dos",'gcd.colonia','gcd.municipio','gcd.estado',"gcd.codigo_postal",'rcc.telefono')
            ->join("nom_cat_nomina as ncn","ncn.id_nomina","=","ne.id_nomina")
            ->join("rh_cat_candidato as rcc","rcc.id_candidato","=","ne.id_candidato")
            ->join("gen_cat_direccion as gcd","gcd.id_direccion","=","rcc.id_direccion")
            ->join("nom_sucursales as ns","ns.id_sucursal","=","ne.id_sucursal")
            ->join("gen_cat_puesto as gcp","gcp.id_puesto","=","ne.id_puesto")
            ->join("gen_cat_cliente as gcc","gcc.id_cliente","=","rcc.id_cliente")
            ->join("gen_cat_departamento as gcdd","gcdd.id_departamento","=","gcp.id_departamento")
            ->where("ne.id_nomina",$this->id_nomina)
            ->whereIn("rcc.id_cliente",$id_clientes)
            ->get();
            return view('formato_alta_mod',compact('datos_empresa','datos'));
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
}