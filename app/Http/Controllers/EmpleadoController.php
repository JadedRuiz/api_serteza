<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Empleado;
use App\Models\Candidato;
use App\Models\Direccion;
use Illuminate\Support\Facades\Storage;

class EmpleadoController extends Controller
{
    public function autocompleteEmpleado(Request $res)
    {
        try{
            $palabra = "%".strtoupper($res["nombre_candidato"])."%";
            $recuperar_id_clientes = DB::table('liga_empresa_cliente')
            ->select("id_cliente")
            ->where("id_empresa",$res["id_empresa"])
            ->get();
            if(count($recuperar_id_clientes)>0){
                $id_clientes = [];
                foreach($recuperar_id_clientes as $id_cliente){
                    array_push($id_clientes,$id_cliente->id_cliente);
                }
                $candidatos = DB::table('rh_cat_candidato as rcc')
                ->join("nom_empleados as ne","ne.id_candidato","=","rcc.id_candidato")
                ->whereIn("rcc.id_cliente",$id_clientes)
                ->where(function ($query) use ($palabra){
                    $query->orWhere(DB::raw('CONCAT(rcc.apellido_paterno, " ", rcc.apellido_materno, " ", rcc.nombre)'),"like",$palabra)
                    ->orWhere("rcc.rfc", "like", $palabra)
                    ->orWhere("rcc.curp", "like", $palabra);
                })
                ->get();
                if(count($candidatos)>0){
                    return $this->crearRespuesta(1,$candidatos,200);
                }else{
                    return $this->crearRespuesta(2,"No se ha encontrado",200);
                }
            }else{
                return $this->crearRespuesta(2,"Está empresa no cuenta con clientes configurados",301);
            }
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function obtenerEmpleadosPorEmpresa(Request $res)    
    {
        try{
            $id_empresa = $res["id_empresa"];
            $id_status = $res["id_status"];
            $pagina = $res["pagina"];
            $take = $res["take"];
            $str = "";
            if($id_status == -1){
                $str = "!=";
            }
            $recuperar_id_clientes = DB::table('liga_empresa_cliente')
            ->select("id_cliente")
            ->where("id_empresa",$res["id_empresa"])
            ->get();
            if(count($recuperar_id_clientes)>0){
                $id_clientes = [];
                foreach($recuperar_id_clientes as $id_cliente){
                    array_push($id_clientes,$id_cliente->id_cliente);
                }
                $empleados = DB::table('nom_empleados as ne')->select("ne.id_empleado","rcc.id_candidato", "rcc.id_fotografia", "rcc.id_status", DB::raw('CONCAT(rcc.apellido_paterno, " ", rcc.apellido_materno, " ", rcc.nombre) as nombre'), "rcc.rfc", "rcc.curp", "rcc.numero_seguro", "rcc.edad", "rcc.fecha_nacimiento", "rcc.correo", "rcc.telefono", "rcc.telefono_dos", "rcc.telefono_tres", "rcc.descripcion","gcd.id_direccion","gcd.calle", "gcd.numero_interior", "gcd.numero_exterior", "gcd.cruzamiento_uno", "gcd.cruzamiento_dos", "gcd.codigo_postal", "gcd.colonia", "gcd.localidad", "gcd.municipio", "gcd.estado", "gcd.descripcion as descripcion_direccion","cf.nombre as fotografia","rcc.id_cliente")
                ->join("rh_cat_candidato as rcc","rcc.id_candidato","=","ne.id_candidato")
                ->join("gen_cat_direccion as gcd","gcd.id_direccion","=","rcc.id_direccion")
                ->join("gen_cat_fotografia as cf","cf.id_fotografia","=","rcc.id_fotografia")
                ->where("id_status",$str,$id_status)
                ->whereIn("rcc.id_cliente",$id_clientes)
                ->skip($pagina)
                ->take($take)
                ->get();
                if(count($empleados)>0){
                    foreach($empleados as $registro){
                        $registro->fotografia = Storage::disk('candidato')->url($registro->fotografia);
                    }
                    return $this->crearRespuesta(1,$empleados,200);
                }else{
                    return $this->crearRespuesta(2,"No se han encontrado candidatos",200);
                }
            }else{
                return $this->crearRespuesta(2,"Está empresa no cuenta con clientes configurados",301);
            }
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function obtenerEmpleadoPorId($id_empleado)
    {
        try{
            $empleado = DB::table('nom_empleados as ne')
            ->join("rh_cat_candidato as rcc","rcc.id_candidato","=","ne.id_candidato")
            ->where("ne.id_empleado",$id_empleado)
            ->get();
            if(count($empleado)>0){
                return $this->crearRespuesta(1,$empleado,200);
            }else{
                return $this->crearRespuesta(2,"No se ha encontrado el empleado",301);
            }
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function crearNuevoEmpleadoConCandidatoExistente(Request $res)
    {
        try{
            $usuario_creacion = $res["usuario_creacion"];
            $fecha = $this->getHoraFechaActual();
            $empleado = new Empleado;
            $empleado->id_candidato = $res["candidato"]["id_candidato"];
            $empleado->id_estatus = 1;
            $empleado->id_nomina = $res["id_nomina"];
            $empleado->id_puesto = $res["id_puesto"];
            $empleado->id_sucursal = $res["id_sucursal"];
            $empleado->id_registropatronal = $res["id_registropatronal"];
            $empleado->id_catbanco = $res["id_catbanco"];
            $empleado->id_contratosat = $res["id_contratosat"];
            $empleado->folio = $res["folio"];
            $empleado->fecha_ingreso = date("Y-m-d",strtotime($res["fecha_ingreso"]));
            $empleado->fecha_antiguedad = date("Y-m-d",strtotime($res["fecha_antiguedad"]));
            $empleado->cuenta = $res["cuenta"];
            $empleado->tarjeta = $res["tarjeta"];
            $empleado->clabe = $res["clabe"];
            $empleado->tipo_salario = $res["tipo_salario"];
            $empleado->jornada = $res["jornada"];
            $empleado->sueldo_integrado = $res["sueldo_integrado"];
            $empleado->sueldo_diario = $res["sueldo_diario"];
            $empleado->sueldo_complemento = $res["sueldo_complemento"];
            $empleado->aplicarsueldoneto = $res["aplicarsueldoneto"];
            $empleado->sinsubsidio = $res["sinsubsidio"];
            $empleado->prestaciones_antiguedad = $res["prestaciones_antiguedad"];
            $empleado->usuario_creacion = $usuario_creacion;
            $empleado->fecha_creacion = $fecha;
            $empleado->save();
            return $this->crearRespuesta(1,"Se ha creado el empleado",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function crearNuevoEmpleado(Request $res)
    {
        //validate incoming request 
        $this->validate($request["candidato"], [
            'rfc' => 'required|string|max:14',
            'curp' => 'required|max:18'
        ]);
        try{
        $fecha = $this->getHoraFechaActual();
        $usuario_creacion = $request["usuario_creacion"];
        $id_cliente = "0";
        //insertar fotografia
        $id_fotografia = $this->getSigId("gen_cat_fotografia");
        //Insertar fotografia
        if($request["candidato"]["fotografia"]["docB64"] == ""){
            //Guardar foto default
            DB::insert('insert into gen_cat_fotografia (id_fotografia, nombre, fecha_creacion, usuario_creacion, activo) values (?,?,?,?,?)', [$id_fotografia,"candidato_default.svg",$fecha,$usuario_creacion,1]);
        }else{
            $file = base64_decode($request["candidato"]["fotografia"]["docB64"]);
            $nombre_image = "Cliente".+$id_cliente."/candidato_img_".$id_fotografia.".".$request["candidato"]["fotografia"]["extension"];
            DB::insert('insert into gen_cat_fotografia (id_fotografia, nombre, fecha_creacion, usuario_creacion, activo) values (?,?,?,?,?)', [$id_fotografia,$nombre_image,$fecha,$usuario_creacion,1]);
            Storage::disk('candidato')->put($nombre_image, $file);
        }
        //Insertar dirección
            $id_direccion = $this->getSigId("gen_cat_direccion");
            $direccion = new Direccion;
            $direccion->id_direccion = $id_direccion;
            $direccion->calle = $request["candidato"]["direccion"]["calle"];
            $direccion->numero_interior = $request["candidato"]["direccion"]["numero_interior"];
            $direccion->numero_exterior = $request["candidato"]["direccion"]["numero_exterior"];
            $direccion->cruzamiento_uno = $request["candidato"]["direccion"]["cruzamiento_uno"];
            $direccion->cruzamiento_dos = $request["candidato"]["direccion"]["cruzamiento_dos"];
            $direccion->codigo_postal = $request["candidato"]["direccion"]["codigo_postal"];
            $direccion->colonia = $request["candidato"]["direccion"]["colonia"];
            $direccion->localidad = $request["candidato"]["direccion"]["localidad"];
            $direccion->municipio = $request["candidato"]["direccion"]["municipio"];
            $direccion->estado = $request["candidato"]["direccion"]["estado"];
            $direccion->descripcion = $request["candidato"]["direccion"]["descripcion"];
            $direccion->fecha_creacion = $fecha;
            $direccion->usuario_creacion = $usuario_creacion;
            $direccion->activo = 1;
            $direccion->save();
            //Insertar candidato
            $id_candidato = $this->getSigId("rh_cat_candidato");
            $canditado = new Candidato;
            $canditado->id_candidato = $id_candidato;
            $canditado->id_status = 1;  //En reclutamiento
            $canditado->id_cliente = $id_cliente;
            $canditado->id_fotografia = $id_fotografia;
            $canditado->id_direccion = $id_direccion;
            $canditado->nombre = strtoupper($request["candidato"]["nombre"]);
            $canditado->apellido_paterno = strtoupper($request["candidato"]["apellido_paterno"]);
            $canditado->apellido_materno = strtoupper($request["candidato"]["apellido_materno"]);
            $canditado->rfc = $request["candidato"]["rfc"];
            $canditado->curp = $request["candidato"]["curp"];
            $canditado->numero_seguro = $request["candidato"]["numero_social"];
            $canditado->fecha_nacimiento = $request["candidato"]["fecha_nacimiento"];
            $canditado->correo = $request["candidato"]["correo"];
            $canditado->telefono =$request["candidato"]["telefono"];
            $canditado->edad = $request["candidato"]["edad"];
            $canditado->telefono_dos =$request["candidato"]["telefono_dos"];
            $canditado->telefono_tres =$request["candidato"]["telefono_tres"];
            $canditado->descripcion = $request["candidato"]["descripcion"];
            $canditado->fecha_creacion = $fecha;
            $canditado->usuario_creacion = $usuario_creacion;
            $canditado->activo = 1;
            $canditado->save();
            //Insertar Empleado
            $empleado->id_candidato = $id_candidato;
            $empleado->id_estatus = $res["id_status"];
            $empleado->id_nomina = $res["id_nomina"];
            $empleado->id_puesto = $res["id_puesto"];
            $empleado->id_sucursal = $res["id_sucursal"];
            $empleado->id_registropatronal = $res["id_registropatronal"];
            $empleado->id_catbanco = $res["id_catbanco"];
            $empleado->id_contratosat = $res["id_contratosat"];
            $empleado->folio = $res["folio"];
            $empleado->fecha_ingreso = $res["fecha_ingreso"];
            $empleado->fecha_antiguedad = $res["fecha_antiguedad"];
            $empleado->cuenta = $res["cuenta"];
            $empleado->tarjeta = $res["tarjeta"];
            $empleado->clabe = $res["clabe"];
            $empleado->tipo_salario = $res["tipo_salario"];
            $empleado->jornada = $res["jornada"];
            $empleado->sueldo_integrado = $res["sueldo_integrado"];
            $empleado->sueldo_diario = $res["sueldo_diario"];
            $empleado->sueldo_complemento = $res["sueldo_complemento"];
            $empleado->aplicarsueldoneto = $res["aplicarsueldoneto"];
            $empleado->sinsubsidio = $res["sinsubsidio"];
            $empleado->prestaciones_antiguedad = $res["prestaciones_antiguedad"];
            $empleado->usuario_creacion = $usuario_creacion;
            $empleado->fecha_creacion = $fecha;
            $empleado->save();
            return $this->crearRespuesta(1,"El empleado se ha creado con éxito",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function modificarEmpleado(Request $res)
    {
        try{
            $empleado = Empleado::find($request["id_empleado"]);
            $fecha = $this->getHoraFechaActual();
            $usuario_modificacion = $request["usuario_creacion"];
            $empleado->id_estatus = $res["id_status"];
            $empleado->id_nomina = $res["id_nomina"];
            $empleado->id_puesto = $res["id_puesto"];
            $empleado->id_sucursal = $res["id_sucursal"];
            $empleado->id_registropatronal = $res["id_registropatronal"];
            $empleado->id_banco = $res["id_banco"];
            $empleado->id_contratosat = $res["id_contratosat"];
            $empleado->folio = $res["folio"];
            $empleado->fecha_ingreso = $res["fecha_ingreso"];
            $empleado->fecha_antiguedad = $res["fecha_antiguedad"];
            $empleado->cuenta = $res["cuenta"];
            $empleado->tarjeta = $res["tarjeta"];
            $empleado->clabe = $res["clabe"];
            $empleado->tipo_salario = $res["tipo_salario"];
            $empleado->jornada = $res["jornada"];
            $empleado->sueldo_integrado = $res["sueldo_integrado"];
            $empleado->sueldo_diario = $res["sueldo_diario"];
            $empleado->sueldo_complemento = $res["sueldo_complemento"];
            $empleado->aplicarsueldoneto = $res["aplicarsueldoneto"];
            $empleado->sinsubsidio = $res["sinsubsidio"];
            $empleado->prestaciones_antiguedad = $res["prestaciones_antiguedad"];
            $empleado->usuario_modificacion = $usuario_modificacion;
            $empleado->fecha_modificacion = $fecha;
            $empleado->save();
            return $this->crearRespuesta(1,"Se ha modificado el empleado",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function obtenerCandidatoPorEmpresa(Request $res)
    {
        try{
            $palabra = "%".strtoupper($res["nombre_candidato"])."%";
            $recuperar_id_clientes = DB::table('liga_empresa_cliente')
            ->select("id_cliente")
            ->where("id_empresa",$res["id_empresa"])
            ->get();
            if(count($recuperar_id_clientes)>0){
                $id_clientes = [];
                foreach($recuperar_id_clientes as $id_cliente){
                    array_push($id_clientes,$id_cliente->id_cliente);
                }
                $candidatos = DB::table('rh_cat_candidato as rcc')
                ->select("rcc.id_candidato", "rcc.id_fotografia", "rcc.id_status", "rcc.apellido_paterno", "rcc.apellido_materno", "rcc.nombre", "rcc.rfc", "rcc.curp", "rcc.numero_seguro", "rcc.edad", "rcc.fecha_nacimiento", "rcc.correo", "rcc.telefono", "rcc.telefono_dos", "rcc.telefono_tres", "rcc.descripcion","gcd.id_direccion","gcd.calle", "gcd.numero_interior", "gcd.numero_exterior", "gcd.cruzamiento_uno", "gcd.cruzamiento_dos", "gcd.codigo_postal", "gcd.colonia", "gcd.localidad", "gcd.municipio", "gcd.estado", "gcd.descripcion as descripcion_direccion","cf.nombre as fotografia","rcc.id_cliente")
                ->join("gen_cat_direccion as gcd","gcd.id_direccion","=","rcc.id_direccion")
                ->join("gen_cat_fotografia as cf","cf.id_fotografia","=","rcc.id_fotografia")
                ->whereIn("rcc.id_cliente",$id_clientes)
                ->where(function ($query) use ($palabra){
                    $query->orWhere(DB::raw('CONCAT(rcc.apellido_paterno, " ", rcc.apellido_materno, " ", rcc.nombre)'),"like",$palabra)
                    ->orWhere("rcc.rfc", "like", $palabra)
                    ->orWhere("rcc.curp", "like", $palabra);
                })
                ->whereIn("rcc.id_status",[1,5])
                ->get();
                if(count($candidatos)>0){
                    $candidatos[0]->fotografia = Storage::disk('candidato')->url($candidatos[0]->fotografia);
                    return $this->crearRespuesta(1,$candidatos,200);
                }else{
                    return $this->crearRespuesta(2,"No se han encontrado candidatos",200);
                }
            }
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
}
