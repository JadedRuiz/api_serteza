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
    public function obtenerDepartamentosPorIdCliente(Request $res)
    {
        $getEmpresa = DB::table('liga_empresa_cliente')
        ->select("id_empresa")
        ->where("id_cliente",$res["id_cliente"])
        ->first();
        $registros = DB::table('gen_cat_departamento as cd')
        ->select("cd.departamento","cd.id_departamento","cd.activo","gce.empresa","gce.id_empresa",DB::raw("CONCAT('(',cd.id_departamento,') ',cd.departamento) as departamento_busqueda"))
        ->join("gen_cat_empresa as gce","gce.id_empresa","=","cd.id_empresa")
        ->where(function($query) use ($res, $getEmpresa){
            if(isset($res["id_empresa"]) && $res["id_empresa"] != null){
                $query->where("gce.id_empresa",$res["id_empresa"]);
            }else{
                $query->where("gce.id_empresa",$getEmpresa->id_empresa);
            }
        })
        ->get();
        if(count($registros)>0){
            foreach($registros as $registro){
                if($registro->activo){
                    $registro->estatus = "Activo";
                }else{
                    $registro->estatus = "Desactivado";
                }
                $puestos = DB::table('gen_cat_puesto')
                ->select("autorizados","id_puesto")
                ->where("id_departamento",$registro->id_departamento)
                ->where("activo",1)
                ->get();
                $autorizados = 0;
                $contratados = 0;
                foreach($puestos as $puesto){
                    $contratados = $contratados + $this->obtenerContratados($puesto->id_puesto);
                    $autorizados = $autorizados + intval($puesto->autorizados);
                }
                $registro->vacantes = $autorizados - $contratados;
                $registro->autorizados = $autorizados;
            }
            return $this->crearRespuesta(1,$registros,200);
        }else{
            return $this->crearRespuesta(2,"No hay departamentos que mostrar",200);
        }
    }
    // public function obtenerDepartamentosPorIdEmpresa($id_empresa)
    // {
    //     $registros = DB::table('gen_cat_departamento as cd')
    //     ->select("cd.departamento","cd.id_departamento")
    //     ->join("liga_empresa_departamento as led","led.id_departamento","=","cd.id_departamento")
    //     ->join("gen_cat_empresa as gce","gce.id_empresa","=","led.id_empresa")
    //     ->where("gce.id_empresa",$id_empresa)
    //     ->get();
    //     if(count($registros)>0){
    //         return $this->crearRespuesta(1,$registros,200);
    //     }else{
    //         return $this->crearRespuesta(2,"No hay departamentos que mostrar",200);
    //     }
    // }
    public function obtenerDepartamentoPorId($id_departamento)
    {
        $registro = DB::table('gen_cat_departamento as cd')
        ->select("cd.id_departamento","cd.id_empresa","cd.departamento","cd.descripcion")
        ->where("cd.id_departamento",$id_departamento)
        ->first();
        if($registro){
            $puestos = DB::table('gen_cat_puesto')
            ->select("id_puesto as id_arreglo","id_puesto","puesto","descripcion","sueldo_tipo_a","sueldo_tipo_b","sueldo_tipo_c","autorizados","puesto as band_puesto")
            ->where("id_departamento",$registro->id_departamento)
            ->where("activo",1)
            ->get();
            $autorizados = 0;
            $contratados = 0;
            foreach($puestos as $puesto){
                $puesto->id_arreglo += 1;
                $num_contratados = $this->obtenerContratados($puesto->id_puesto);
                $puesto->band_puesto = false;
                if($num_contratados > 0){
                    $puesto->band_puesto = true;
                }
                $contratados += $num_contratados;
                $autorizados += intVal($puesto->autorizados);
            }
            $registro->puestos = $puestos;
            $registro->vacantes = $autorizados - $contratados;
            $registro->autorizados = $autorizados;
            return $this->crearRespuesta(1,$registro,200);
        }else{
            return $this->crearRespuesta(2,"No hay departamento que mostrar",200);
        }
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
    public function altaDepartamento(Request $request)
    {
        $fecha = $this->getHoraFechaActual();
        try{
            $departamento = new Departamento;
            $departamento->id_empresa = $request["id_empresa"];
            $departamento->departamento = strtoupper($request["departamento"]);
            $departamento->descripcion = strtoupper($request["descripcion"]);
            $departamento->fecha_creacion = $fecha;
            $departamento->usuario_creacion = $request["usuario_creacion"];
            $departamento->activo = 1;
            $departamento->save();
            $id_departamento = $departamento->id_departamento;
            foreach($request["puestos"] as $puesto){
                $puesto_clase = new Puesto;
                $puesto_clase->id_departamento = $id_departamento;
                $puesto_clase->puesto = strtoupper($puesto["puesto"]);
                $puesto_clase->autorizados = $puesto["autorizados"];
                $puesto_clase->descripcion = strtoupper($puesto["descripcion"]);
                $puesto_clase->fecha_creacion = $fecha;
                $puesto["sueldo_tipo_a"] = str_replace("$","",$puesto["sueldo_tipo_a"]);
                $puesto["sueldo_tipo_a"] = str_replace(",","",$puesto["sueldo_tipo_a"]);
                $puesto_clase->sueldo_tipo_a = $puesto["sueldo_tipo_a"];
                $puesto["sueldo_tipo_b"] = str_replace("$","",$puesto["sueldo_tipo_b"]);
                $puesto["sueldo_tipo_b"] = str_replace(",","",$puesto["sueldo_tipo_b"]);
                $puesto_clase->sueldo_tipo_b = $puesto["sueldo_tipo_b"];
                $puesto["sueldo_tipo_c"] = str_replace("$","",$puesto["sueldo_tipo_c"]);
                $puesto["sueldo_tipo_c"] = str_replace(",","",$puesto["sueldo_tipo_c"]);
                $puesto_clase->sueldo_tipo_c = $puesto["sueldo_tipo_c"];
                $puesto_clase->usuario_creacion = $request["usuario_creacion"];
                $puesto_clase->activo = 1;
                $puesto_clase->save();
            }
            return $this->crearRespuesta(1,"Departamento registrado",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function modificarDepartamento(Request $request)
    {
        $fecha = $this->getHoraFechaActual();
        $errores = [];
        try{
            $departamento = Departamento::find($request["id_departamento"]);
            $departamento->departamento = strtoupper($request["departamento"]);
            $departamento->descripcion = strtoupper($request["descripcion"]);
            $departamento->fecha_modificacion = $fecha;
            $departamento->usuario_modificacion = $request["usuario_creacion"];
            $departamento->activo = 1;
            $departamento->save();
            $id_departamento = $departamento->id_departamento;
            foreach($request["puestos"] as $puesto){
                if($puesto["id_puesto"] != "0"){
                    $puesto_clase = Puesto::find($puesto["id_puesto"]);
                    $contratados = $this->obtenerContratados($puesto["id_puesto"]);
                    $vacantes = $puesto_clase->autorizados - $contratados;
                    if($vacantes < $puesto["autorizados"]){
                        array_push($errores,"No se pudo actualizar el número de autorizados, el valor no puede ser menor al número de vacantes");
                    }else{
                        $puesto_clase->autorizados = $puesto["autorizados"];
                    }
                }else{
                    $puesto_clase = new Puesto();
                    $puesto_clase->autorizados = $puesto["autorizados"];
                }
                $puesto_clase->id_departamento = $id_departamento;
                $puesto_clase->puesto = strtoupper($puesto["puesto"]);
                $puesto_clase->descripcion = strtoupper($puesto["descripcion"]);
                $puesto_clase->fecha_modificacion = $fecha;
                $puesto["sueldo_tipo_a"] = str_replace("$","",$puesto["sueldo_tipo_a"]);
                $puesto["sueldo_tipo_a"] = str_replace(",","",$puesto["sueldo_tipo_a"]);
                $puesto_clase->sueldo_tipo_a = $puesto["sueldo_tipo_a"];
                $puesto["sueldo_tipo_b"] = str_replace("$","",$puesto["sueldo_tipo_b"]);
                $puesto["sueldo_tipo_b"] = str_replace(",","",$puesto["sueldo_tipo_b"]);
                $puesto_clase->sueldo_tipo_b = $puesto["sueldo_tipo_b"];
                $puesto["sueldo_tipo_c"] = str_replace("$","",$puesto["sueldo_tipo_c"]);
                $puesto["sueldo_tipo_c"] = str_replace(",","",$puesto["sueldo_tipo_c"]);
                $puesto_clase->sueldo_tipo_c = $puesto["sueldo_tipo_c"];
                $puesto_clase->usuario_modificacion = $request["usuario_creacion"];
                $puesto_clase->activo = 1;
                $puesto_clase->save();
            }
            if(count($errores) > 0){
                return $this->crearRespuesta(1,["tipo" => 1, "data" => $errores],200);
            }
            return $this->crearRespuesta(1,["tipo" => 2, "data" => "Departamento actualizado"],200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
   public function eliminarPuesto($id_puesto)
   {
       try{
        DB::update('update gen_cat_puesto set activo = 0 where id_puesto = ?', [$id_puesto]);
        return $this->crearRespuesta(1,"Puesto eliminado",200);
       }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
   }
}
