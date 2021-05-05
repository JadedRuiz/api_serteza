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
}
