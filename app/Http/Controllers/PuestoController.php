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
            return $this->crearRespuesta(2,"No hay puestos que mostrar",200);
        }
    }
    public function obtenerPuestosPorIdDepartamento($id_departamento)
    {
        $puestos = Puesto::where("id_departamento",$id_departamento)->get();
        if(count($puestos)>0){
            $autorizados = 0;
            $contratados = 0;
            foreach($puestos as $puesto){
                $puesto->vacantes = intval($puesto->autorizados) - intval($puesto->contratados);
                if($puesto->contratados != null){
                    $puesto->contratados = $contratados + intval($puesto->contratados);
                }else{
                    $puesto->contratados = 0;
                }
                $puesto->autorizados = $autorizados + intval($puesto->autorizados);
            }
            return $this->crearRespuesta(1,$puestos,200);
        }else{
            return $this->crearRespuesta(2,"No hay puestos que mostrar",200);
        }
    }
    public function eliminarPuesto($id_puesto){
        try{
            DB::update('update gen_cat_puesto set activo = 0 where id_puesto = ?', [$id_puesto]);
            return $this->crearRespuesta(1,"Elemento eliminado",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function obtenerPuestosPorEmpresa($id_empresa)
    {
        $recuperar_departamentos_empresa = DB::table('liga_empresa_departamento')
        ->select("id_departamento")
        ->where("id_empresa",$id_empresa)
        ->get();
        if(count($recuperar_departamentos_empresa)>0){
            $id_departamentos = [];
            foreach($recuperar_departamentos_empresa as $id_departamento){
                array_push($id_departamentos,$id_departamento->id_departamento);
            }
            $puestos = DB::table('gen_cat_puesto')
            ->select("id_puesto","puesto")
            ->whereIn("id_departamento",$id_departamentos)
            ->get();
            if(count($puestos)>0){
                return $this->crearRespuesta(1,$puestos,200);
            }else{
                return $this->crearRespuesta(2,"No se tienen puestos",200);
            }
        }else{
            return $this->crearRespuesta(2,"No se ha recuperado ningun departamento",200);
        }
    }
}
