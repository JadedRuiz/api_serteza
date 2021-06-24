<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ProveedorClienteController extends Controller
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
    public function actualizarPC(){
        DB::update('update con_provcliente set id_concepto = ?, 
                                fecha_modificacion = ?, usuario_modificacion = ?
                                where id_provcliente = ?', 
                                [$miConcepto, $this->getHoraFechaActual(), $usuario, $id_provcliente]);
        DB::update('update con_movfacturas set id_concepto =  ?
                    where id_provcliente = ?', 
                    [1, 1]);
    }

}
