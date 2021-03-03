<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class EmpresaController extends Controller
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

    public function obtenerEmpresa($sistema_id){
        $empresas = DB::table("liga_usuario_empresa as lue")
        ->join("gen_cat_empresas as gce","gce.id","=","lue.cat_empresas_id")
        ->select("gce.id","gce.empresa")
        ->where("lue.usuario_sistemas_id",$sistema_id)
        ->where("lue.activo",1)
        ->get();
        if(count($empresas)>0){
            return $this->crearRespuesta(1,$empresas,200);
        }else{
            return $this->crearRespuesta(2,"No se han encontrado empresas configuradas en su usuario",200);
        }
    }
}
