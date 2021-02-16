<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ClienteController extends Controller
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

    function obtenerClientes($usuario_empresa_id){
        $cliente = DB::table("liga_usuario_cliente as luc")
        ->join("gen_cat_cliente as gcc","gcc.id","=","luc.cliente_id")
        ->select("gcc.id","gcc.cliente")
        ->where("luc.usuario_empresa_id",1)
        ->get();
        if(count($cliente)>0){
            return $this->crearRespuesta(1,$cliente,200);
        }else{
            return $this->crearRespuesta(2,"No se han encontrado clientes configuradas en su usuario",200);
        }
    }
}