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
    public function autoComplete(Request $res){
        $palabra = strtoupper($res["nombre_departamento"]);
        $id_empresa = $res["id_empresa"];
        $busqueda = DB::table('gen_cat_departamento as cd')
        ->select("cd.departamento","cd.id_departamento")
        ->join("liga_empresa_departamento as led","led.id_departamento","=","cd.id_departamento")
        ->where("led.activo",1)
        ->where("cd.departamento","like","%".$palabra."%")
        ->where("led.id_empresa",$id_empresa)
        ->take(5)
        ->get();
        if(count($busqueda)>0){
            return $this->crearRespuesta(1,$busqueda,200);
        }
        return $this->crearRespuesta(2,"No se han encontrado resultados",200);
    }
    public function obtenerDepartamentos(Request $res){
        $take = $res["taken"];
        $pagina = $res["pagina"];
        $status = $res["status"];
        $palabra = $res["palabra"];
        $id_empresa = $res["id_empresa"];
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
        $registros = DB::table('gen_cat_departamento as cd')
        ->select("cd.departamento","cd.id_departamento","led.activo")
        ->join("liga_empresa_departamento as led","led.id_departamento","=","cd.id_departamento")
        ->where("led.activo",$otro,$status)
        ->where("cd.departamento",$otro_dos,$palabra)
        ->where("led.id_empresa",$id_empresa)
        ->skip($incia)
        ->take($take)
        ->get();
        $contar = DB::table('gen_cat_departamento as cd')
        ->join("liga_empresa_departamento as led","led.id_departamento","=","cd.id_departamento")
        ->where("led.activo",$otro,$status)
        ->where("cd.departamento",$otro_dos,$palabra)
        ->where("led.id_empresa",$id_empresa)
        ->get();
        if(count($registros)>0){
            foreach($registros as $registro){
                $puestos = DB::table('gen_cat_puesto')
                ->select("autorizados","contratados")
                ->where("id_departamento",$registro->id_departamento)
                ->get();
                $autorizados = 0;
                $contratados = 0;
                foreach($puestos as $puesto){
                    if($puesto->contratados != null){
                        $contratados = $contratados + intval($puesto->contratados);
                    }
                    $autorizados = $autorizados + intval($puesto->autorizados);
                }
                $registro->vacantes = $autorizados - $contratados;
                $registro->autorizados = $autorizados;
            }
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
        $departamento = DB::table('gen_cat_departamento as cd')
        ->select("cd.id_departamento","cd.departamento","cd.descripcion","cd.activo as puestos","cd.activo")
        ->where("cd.id_departamento",$id_departamento)
        ->get();
        if(count($departamento)>0){
            $puestos = DB::table('gen_cat_puesto as cp')
            ->select("autorizados","contratados","cp.id_puesto","cp.puesto","cp.descripcion","cp.sueldo_tipo_a","cp.sueldo_tipo_b","cp.sueldo_tipo_c")
            ->where("id_departamento",$id_departamento)
            ->where("activo",1)
            ->get();
            $autorizados = 0;
            $contratados = 0;
            foreach($puestos as $puesto){
                if($puesto->contratados != null){
                    $contratados = $contratados + intval($puesto->contratados);
                }
                $autorizados = $autorizados + intval($puesto->autorizados);
            }
            $departamento[0]->vacantes = $autorizados - $contratados;
            $departamento[0]->autorizados = $autorizados;
            $departamento[0]->puestos = $puestos;
            return $this->crearRespuesta(1,$departamento,200);
        }else{
            return $this->crearRespuesta(2,"No hay departamentos que mostrar",200);
        }
    }
    public function altaDepartamento(Request $request){
        try{
            $fecha = $this->getHoraFechaActual();
            $id_empresa = $request["id_empresa"];
            //Alta departamento
            $id_departamento = $this->getSigId("gen_cat_departamento");
            $departamento = new Departamento;
            $departamento->id_departamento = $id_departamento;
            $departamento->departamento = strtoupper($request["departamento"]);
            $departamento->disponibilidad = $request["disponibilidad"];
            $departamento->descripcion = $request["descripcion"];
            $departamento->fecha_creacion = $fecha;
            $departamento->usuario_creacion = $request["usuario_creacion"];
            $departamento->activo = $request["activo"];
            $departamento->save();
            //Alta puestos del departamento
            $puestos = $request["puestos"];
            foreach($puestos as $puesto){
                $id_puesto = $this->getSigId("gen_cat_puesto");
                $puesto_clase = new Puesto;
                $puesto_clase->id_puesto = $id_puesto;
                $puesto_clase->id_departamento = $id_departamento;
                $puesto_clase->puesto = strtoupper($puesto["puesto"]);
                $puesto_clase->disponibilidad = $puesto["disponibilidad"];
                $puesto_clase->descripcion = $puesto["descripcion"];
                $puesto_clase->fecha_creacion = $fecha;
                $puesto_clase->sueldo_tipo_a = $puesto["sueldo_tipo_a"];
                $puesto_clase->sueldo_tipo_b = $puesto["sueldo_tipo_b"];
                $puesto_clase->sueldo_tipo_c = $puesto["sueldo_tipo_c"];
                $puesto_clase->usuario_creacion = $request["usuario_creacion"];
                $puesto_clase->activo = 1;
                $puesto_clase->save();
            }
            //Ligar departamento con empresa
            $id_empresa_departamento = $this->getSigId("liga_empresa_departamento");
            DB::insert('insert into liga_empresa_departamento (id_empresa_departamento, id_empresa, id_departamento, fecha_creacion, usuario_creacion, activo) values (?, ?, ?, ?, ?, ?)', [$id_empresa_departamento, $id_empresa, $id_departamento, $fecha, $request["usuario_creacion"], 1]);
            return $this->crearRespuesta(1,"Departamento registrado",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function actualizarDepartamento(Request $request){
        try{
            $departamento = Departamento::find($request["id_departamento"]);
            $departamento->departamento = strtoupper($request["departamento"]);
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
                $puesto_clase->puesto = strtoupper($puesto["puesto"]);
                $puesto_clase->disponibilidad = $puesto["disponibilidad"];
                $puesto_clase->descripcion = $puesto["descripcion"];
                $puesto_clase->sueldo_tipo_a = $puesto["sueldo_tipo_a"];
                $puesto_clase->sueldo_tipo_b = $puesto["sueldo_tipo_b"];
                $puesto_clase->sueldo_tipo_c = $puesto["sueldo_tipo_c"];
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
