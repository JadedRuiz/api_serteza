<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Departamento;

class DepartamentoController extends Controller
{
    
    public function __construct()
    {
        //
    }

    public function obtenerDepartamentos($cat_empresa_id){
        $respuesta = Departamento::where("cat_empresa_id",$cat_empresa_id)->where("activo",1)->get();
        return $this->crearRespuesta(1,$respuesta,200);
    }
    public function obtenerDepartamentoPorId($id){
        $respuesta = Departamento::where("id",$id)->where("activo",1)->get();
        return $this->crearRespuesta(1,$respuesta,200);
    }
    public function altaDepartamento(Request $request){
        try{
            //Alta departamento
            $id_depa = $this->getSigId("gen_cat_departamentos");
            $departamento = new Departamento;
            $departamento->id = $id_depa;
            $departamento->cat_empresa_id = $request["cat_empresa_id"];
            $departamento->departamento = $request["nombre"];
            $departamento->disponibilidad = $request["disponibilidad"];
            $departamento->descripcion = $request["descripcion"];
            $departamento->fecha_creacion = $this->getHoraFechaActual();
            $departamento->cat_usuario_c_id = $request["cat_usuario_id"];
            $departamento->activo = 1;
            $departamento->save();
            return $this->crearRespuesta(1,"Departamento registrado",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function actualizarDepartamento(Request $request){
        try{
            $departamento = Departamento::find($request["id"]);
            $departamento->cat_empresa_id = $request["cat_empresa_id"];
            $departamento->departamento = $request["nombre"];
            $departamento->disponibilidad = $request["disponibilidad"];
            $departamento->descripcion = $request["descripcion"];
            $departamento->fecha_modificacion = $this->getHoraFechaActual();
            $departamento->cat_usuario_m_id = $request["cat_usuario_id"];
            $departamento->activo = 1;
            $departamento->save();
            return $this->crearRespuesta(1,"Departamento actualizado",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function bajaDepartamento($id){
        try{
            $data = DB::update('update gen_cat_departamentos set activo = 0 where id = ?',[$id]);
            return $this->crearRespuesta(1,"Departamento Eliminado",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
}
