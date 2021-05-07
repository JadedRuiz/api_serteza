<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Puesto;

class PuestoController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function getPuestosPorIdEmpresa($id_empresa)
    {
        $puestos = Puesto::where("id_empresa",$id_empresa)->get();
        if(count($puestos)>0){
            return $this->crearRespuesta(1,$puestos,200);
        }else{
            return $this->crearRespuesta(2,"No hay empresas que mostrar",200);
        }
    }
    public function eliminarPuesto($id_puesto){
        try{
            DB::update('update cat_puesto set activo = 0 where id_puesto = ?', [$id_puesto]);
            return $this->crearRespuesta(1,"Elemento eliminado",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
}
