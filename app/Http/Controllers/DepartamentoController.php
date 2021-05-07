<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Departamento;
use App\Models\Puesto;

class DepartamentoController extends Controller
{
    
    public function __construct()
    {
        //
    }

    public function obtenerDepartamentos(Request $res){
        $take = $res["taken"];
        $pagina = $res["pagina"];
        $status = $res["status"];
        $palabra = $res["palabra"];
        $otro = "";
        if($status == "2"){
            $otro = "!=";
            $status = 2;
        }
        if($status == "1"){
            $status = 1;
            $otro = "=";
        }
        if($status == "0"){
            $status = 0;
            $otro = "=";
        }
        if($palabra == ""){
            $otro_dos = "!=";
            $palabra = "";
        }else{
            $otro_dos = "like";
            $palabra = "%".$palabra."%";
        }
        $incia = intval($pagina) * intval($take);
        $registros = DB::table('cat_departamento')
        ->where("activo",$otro,$status)
        ->where("departamento",$otro_dos,$palabra)
        ->skip($incia)
        ->take($take)
        ->get();
        $contar = DB::table('cat_departamento')
        ->where("activo",$otro,$status)
        ->where("departamento",$otro_dos,$palabra)
        ->get();
        if(count($registros)>0){
            $respuesta = [
                "total" => count($contar),
                "registros" => $registros
            ];
            return $this->crearRespuesta(1,$respuesta,200);
        }else{
            return $this->crearRespuesta(2,"No hay departamentos que mostrar",200);
        }
    }
    public function obtenerDepartamentoPorId($id){
        $respuesta = Departamento::where("id_departamento",$id)->where("activo",1)->get();
        return $this->crearRespuesta(1,$respuesta,200);
    }
    public function obtenerDepartamentoPorIdDepartamento($id_departamento){
        $departamento = DB::table('cat_departamento as cd')
        ->select("cd.id_departamento","cd.departamento","cd.descripcion","cd.disponibilidad","cd.activo as puestos","cd.activo")
        ->where("cd.id_departamento",$id_departamento)
        ->get();
        $puestos = DB::table('cat_puesto as cp')
        ->select("cp.id_puesto","cp.puesto","cp.descripcion","cp.disponibilidad")
        ->where("cp.id_departamento",$id_departamento)
        ->where("activo",1)
        ->get();
        if(count($departamento)>0){
            $departamento[0]->puestos = $puestos;
            return $this->crearRespuesta(1,$departamento,200);
        }else{
            return $this->crearRespuesta(2,"No hay departamentos que mostrar",200);
        }
    }
    public function altaDepartamento(Request $request){
        try{
            //Alta departamento
            $id_departamento = $this->getSigId("cat_departamento");
            $departamento = new Departamento;
            $departamento->id_departamento = $id_departamento;
            $departamento->departamento = $request["departamento"];
            $departamento->disponibilidad = $request["disponibilidad"];
            $departamento->descripcion = $request["descripcion"];
            $departamento->fecha_creacion = $this->getHoraFechaActual();
            $departamento->usuario_creacion = $request["usuario_creacion"];
            $departamento->activo = $request["activo"];
            $departamento->save();
            //Alta puestos del departamento
            $puestos = $request["puestos"];
            foreach($puestos as $puesto){
                $id_puesto = $this->getSigId("cat_puesto");
                $puesto_clase = new Puesto;
                $puesto_clase->id_puesto = $id_puesto;
                $puesto_clase->id_departamento = $id_departamento;
                $puesto_clase->puesto = $puesto["puesto"];
                $puesto_clase->disponibilidad = $puesto["disponibilidad"];
                $puesto_clase->descripcion = $puesto["descripcion"];
                $puesto_clase->fecha_creacion = $this->getHoraFechaActual();
                $puesto_clase->usuario_creacion = $request["usuario_creacion"];
                $puesto_clase->activo = 1;
                $puesto_clase->save();
            }
            return $this->crearRespuesta(1,"Departamento registrado",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function actualizarDepartamento(Request $request){
        try{
            $departamento = Departamento::find($request["id_departamento"]);
            $departamento->departamento = $request["departamento"];
            $departamento->disponibilidad = $request["disponibilidad"];
            $departamento->descripcion = $request["descripcion"];
            $departamento->fecha_modificacion = $this->getHoraFechaActual();
            $departamento->usuario_modificacion = $request["usuario_creacion"];
            $departamento->activo = $request["activo"];
            $departamento->save();
            //Alta puestos del departamento
            $puestos = $request["puestos"];
            foreach($puestos as $puesto){
                $puesto_clase = Puesto::find($puesto["id_puesto"]);
                $puesto_clase->puesto = $puesto["puesto"];
                $puesto_clase->disponibilidad = $puesto["disponibilidad"];
                $puesto_clase->descripcion = $puesto["descripcion"];
                $puesto_clase->fecha_modificacion = $this->getHoraFechaActual();
                $puesto_clase->usuario_modificacion = $request["usuario_creacion"];
                $puesto_clase->activo = 1;
                $puesto_clase->save();
            }
            return $this->crearRespuesta(1,"Departamento actualizado",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
}
