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
                ->select("ne.id_empleado",DB::raw('CONCAT(rcc.apellido_paterno, " ", rcc.apellido_materno, " ", rcc.nombre) as nombre'))
                ->join("nom_empleados as ne","ne.id_candidato","=","rcc.id_candidato")
                ->whereIn("rcc.id_cliente",$id_clientes)
                ->where(function ($query) use ($palabra){
                    $query->orWhere(DB::raw('CONCAT(rcc.apellido_paterno, " ", rcc.apellido_materno, " ", rcc.nombre)'),"like",$palabra)
                    ->orWhere("rcc.rfc", "like", $palabra)
                    ->orWhere("rcc.curp", "like", $palabra)
                    ->orWhere("ne.folio", "like", $palabra);
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
            $tipo_nomina = $res["id_nomina"];
            $pagina = intval($res["pagina"])*5;
            $take = $res["take"];
            $str = "=";
            $str_nomina = "=";
            if($tipo_nomina == -1){
                $str_nomina = "!=";
            }
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
                $empleados = DB::table('nom_empleados as ne')
                ->select("ne.id_empleado","rcc.id_candidato", "rcc.id_fotografia", "ne.id_estatus", DB::raw('CONCAT(rcc.apellido_paterno, " ", rcc.apellido_materno, " ", rcc.nombre) as nombre'), "rcc.rfc", "rcc.curp", "rcc.numero_seguro", "rcc.edad", "rcc.fecha_nacimiento", "rcc.correo", "rcc.telefono", "rcc.telefono_dos", "rcc.telefono_tres", "rcc.descripcion","gcd.id_direccion","gcd.calle", "gcd.numero_interior", "gcd.numero_exterior", "gcd.cruzamiento_uno", "gcd.cruzamiento_dos", "gcd.codigo_postal", "gcd.colonia", "gcd.localidad", "gcd.municipio", "gcd.estado", "gcd.descripcion as descripcion_direccion","cf.nombre as fotografia","rcc.id_cliente")
                ->join("rh_cat_candidato as rcc","rcc.id_candidato","=","ne.id_candidato")
                ->join("gen_cat_direccion as gcd","gcd.id_direccion","=","rcc.id_direccion")
                ->join("gen_cat_fotografia as cf","cf.id_fotografia","=","rcc.id_fotografia")
                ->where("rcc.id_status",$str,$id_status)
                ->where("ne.id_nomina",$str_nomina,$tipo_nomina)
                ->whereIn("rcc.id_cliente",$id_clientes)
                ->skip($pagina)
                ->take($take)
                ->get();
                $total = DB::table('nom_empleados as ne')
                ->select("ne.id_empleado","rcc.id_candidato", "rcc.id_fotografia", "ne.id_estatus", DB::raw('CONCAT(rcc.apellido_paterno, " ", rcc.apellido_materno, " ", rcc.nombre) as nombre'), "rcc.rfc", "rcc.curp", "rcc.numero_seguro", "rcc.edad", "rcc.fecha_nacimiento", "rcc.correo", "rcc.telefono", "rcc.telefono_dos", "rcc.telefono_tres", "rcc.descripcion","gcd.id_direccion","gcd.calle", "gcd.numero_interior", "gcd.numero_exterior", "gcd.cruzamiento_uno", "gcd.cruzamiento_dos", "gcd.codigo_postal", "gcd.colonia", "gcd.localidad", "gcd.municipio", "gcd.estado", "gcd.descripcion as descripcion_direccion","cf.nombre as fotografia","rcc.id_cliente")
                ->join("rh_cat_candidato as rcc","rcc.id_candidato","=","ne.id_candidato")
                ->join("gen_cat_direccion as gcd","gcd.id_direccion","=","rcc.id_direccion")
                ->join("gen_cat_fotografia as cf","cf.id_fotografia","=","rcc.id_fotografia")
                ->where("rcc.id_status",$str,$id_status)
                ->where("ne.id_nomina",$str_nomina,$tipo_nomina)
                ->whereIn("rcc.id_cliente",$id_clientes)
                ->get()
                ->count();
                if(count($empleados)>0){
                    foreach($empleados as $registro){
                        $registro->fotografia = Storage::disk('candidato')->url($registro->fotografia);
                    }
                    return $this->crearRespuesta(1,["data"=>$empleados,"total" => $total],200);
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
            ->select("ne.id_empleado", "rcc.id_candidato", "rcc.apellido_paterno", "rcc.apellido_materno", "rcc.nombre","rcc.rfc", "rcc.curp", "rcc.numero_seguro", "rcc.edad", "rcc.fecha_nacimiento", "rcc.correo", "rcc.telefono", "rcc.telefono_dos", "rcc.telefono_tres", "rcc.descripcion","gcd.id_direccion","gcd.calle", "gcd.numero_interior", "gcd.numero_exterior", "gcd.cruzamiento_uno", "gcd.cruzamiento_dos", "gcd.codigo_postal", "gcd.colonia", "gcd.localidad", "gcd.municipio", "gcd.estado", "gcd.descripcion as descripcion_direccion","gcf.nombre as fotografia","rcc.id_cliente", "ne.id_puesto","ne.id_sucursal","ne.id_registropatronal","ne.id_catbanco","ne.id_contratosat","ne.fecha_ingreso","ne.fecha_antiguedad","ne.cuenta","ne.tarjeta","ne.clabe","ne.tipo_salario","ne.jornada","ne.sueldo_diario","ne.sueldo_integrado","ne.sueldo_complemento","ne.aplicarsueldoneto","ne.sinsubsidio","ne.prestaciones_antiguedad","rcc.id_fotografia")
            ->join("rh_cat_candidato as rcc","rcc.id_candidato","=","ne.id_candidato")
            ->join("gen_cat_direccion as gcd","gcd.id_direccion","=","rcc.id_direccion")
            ->join("gen_cat_fotografia as gcf","gcf.id_fotografia","=","rcc.id_fotografia")
            ->where("ne.id_empleado",$id_empleado)
            ->get();
            if(count($empleado)>0){
                $empleado[0]->fotografia = Storage::disk('candidato')->url($empleado[0]->fotografia);
                return $this->crearRespuesta(1,$empleado,200);
            }else{
                return $this->crearRespuesta(2,"No se ha encontrado el empleado",301);
            }
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function obtenerEmpleadoPorIdCandidato($id_candidato)
    {
        $empleado = DB::table('nom_empleados as ne')
        ->select("ne.id_empleado",DB::raw("CONCAT('(',gce.id_empresa,')',' ',gce.empresa) as empresa"),DB::raw("CONCAT('(',gcd.id_departamento,')',' ',gcd.departamento) as departamento"),"gcp.puesto","ne.sueldo_diario","ne.sueldo_integrado","ne.id_nomina","sucursal","ne.fecha_ingreso","ne.descripcion","gcf.nombre as url_foto","ncs.id_sucursal","gcd.id_empresa","gcd.id_departamento","gcp.id_puesto","ncn.nomina")
        ->join("rh_cat_candidato as rcc","rcc.id_candidato","=","ne.id_candidato")
        ->join("gen_cat_fotografia as gcf","gcf.id_fotografia","=","rcc.id_fotografia")
        ->join("gen_cat_puesto as gcp","gcp.id_puesto","=","ne.id_puesto")
        ->join("gen_cat_departamento as gcd","gcd.id_departamento","=","gcp.id_departamento")
        ->join("gen_cat_empresa as gce","gce.id_empresa","=","gcd.id_empresa")
        ->join("nom_sucursales as ncs","ncs.id_sucursal","=","ne.id_sucursal")
        ->join("nom_cat_nomina as ncn","ncn.id_nomina","=","ne.id_nomina")
        ->where("ne.id_candidato",$id_candidato)
        ->where("ne.id_estatus",1)
        ->first();
        
        if($empleado){
            $empleado->url_foto = Storage::disk('candidato')->url($empleado->url_foto);
            $empleado->sueldo_diario = "$".number_format($empleado->sueldo_diario,2,'.',',');
            $empleado->sueldo_integrado = "$".number_format($empleado->sueldo_integrado,2,'.',',');
            return $this->crearRespuesta(1,$empleado,200);
        }
        return $this->crearRespuesta(2,"No se ha econtrado el empleado",200);
    }
    public function obtenerEmpleadoPorTipoNomina(Request $res)
    {
        $id_nomina = $res["id_nomina"];
        $id_sucursal = $res["id_sucursal"];
        $id_departamento = $res["id_departamento"];
        $palabra = "%".strtoupper($res["palabra"])."%";
        $id_empresa = $res["id_empresa"];
        $str_nomina = "=";
        $tipo = $res["tipo"];
        if($id_nomina == -1){
            $str_nomina = "!=";
        }
        $str_depa = "=";
        if($id_departamento == -1){
            $str_depa = "!=";
        }
        $str_sucursal = "=";
        if($id_sucursal == -1){
            $str_sucursal = "!=";
        }
        if(isset($res["id_cliente"])){
            $empleados = DB::table('nom_empleados as ne')
            ->select("ne.id_empleado as folio","ne.id_empleado", DB::raw('CONCAT(rcc.apellido_paterno, " ", rcc.apellido_materno, " ", rcc.nombre) as nombre'),"cf.nombre as fotografia","gcc.cliente","ns.sucursal","gcd.departamento","ne.id_estatus","ns.id_empresa")
            ->join("rh_cat_candidato as rcc","rcc.id_candidato","=","ne.id_candidato")
            ->join("gen_cat_fotografia as cf","cf.id_fotografia","=","rcc.id_fotografia")
            ->join("gen_cat_puesto as gcp","gcp.id_puesto","=","ne.id_puesto")
            ->join("gen_cat_departamento as gcd","gcd.id_departamento","=","gcp.id_departamento")
            ->join("nom_sucursales as ns","ns.id_sucursal","=","ne.id_sucursal")
            ->join("gen_cat_cliente as gcc","gcc.id_cliente","=","ns.id_cliente")
            ->where("ne.id_nomina",$str_nomina,$res["id_nomina"])
            ->where("ne.id_estatus","1")
            ->where(function ($query) use ($tipo,$palabra){
                if($tipo == 2){
                    $query->where(DB::raw('CONCAT(rcc.apellido_paterno, " ", rcc.apellido_materno, " ", rcc.nombre)'),"like",$palabra);
                }
            })
            ->where("gcp.id_departamento",$str_depa,$id_departamento)
            ->where("ne.id_sucursal",$str_sucursal,$id_sucursal)
            ->where("rcc.id_cliente",$res["id_cliente"])
            ->get();
            if(count($empleados)>0){
                if($tipo == 1){
                    foreach($empleados as $registro){
                        $registro->fotografia = Storage::disk('candidato')->url($registro->fotografia);
                    }
                }
                return $this->crearRespuesta(1,$empleados,200);
            }else{
                return $this->crearRespuesta(2,"No se han encontrado candidatos",200);
            }
        }else{
            $recuperar_id_clientes = DB::table('liga_empresa_cliente')
            ->select("id_cliente")
            ->where("id_empresa",$id_empresa)
            ->get();
            if(count($recuperar_id_clientes)>0){
                $id_clientes = [];
                foreach($recuperar_id_clientes as $id_cliente){
                    array_push($id_clientes,$id_cliente->id_cliente);
                }
                $empleados = DB::table('nom_empleados as ne')
                ->select("ne.id_empleado as folio","ne.id_empleado", DB::raw('CONCAT(rcc.apellido_paterno, " ", rcc.apellido_materno, " ", rcc.nombre) as nombre'),"cf.nombre as fotografia","gcc.cliente","ns.sucursal","gcd.departamento","ne.id_estatus")
                ->join("rh_cat_candidato as rcc","rcc.id_candidato","=","ne.id_candidato")
                ->join("gen_cat_fotografia as cf","cf.id_fotografia","=","rcc.id_fotografia")
                ->join("gen_cat_puesto as gcp","gcp.id_puesto","=","ne.id_puesto")
                ->join("gen_cat_departamento as gcd","gcd.id_departamento","=","gcp.id_departamento")
                ->join("nom_sucursales as ns","ns.id_sucursal","=","ne.id_sucursal")
                ->join("gen_cat_cliente as gcc","gcc.id_cliente","=","ns.id_cliente")
                ->where("ne.id_nomina",$str_nomina,$res["id_nomina"])
                ->where("ne.id_estatus","1")
                ->where(function ($query) use ($tipo,$palabra){
                    if($tipo == 2){
                        $query->where(DB::raw('CONCAT(rcc.apellido_paterno, " ", rcc.apellido_materno, " ", rcc.nombre)'),"like",$palabra);
                    }
                })
                ->where("gcp.id_departamento",$str_depa,$id_departamento)
                ->where("ne.id_sucursal",$str_sucursal,$id_sucursal)
                ->whereIn("rcc.id_cliente",$id_clientes)
                ->get();
                if(count($empleados)>0){
                    if($tipo == 1){
                        foreach($empleados as $registro){
                            $registro->fotografia = Storage::disk('candidato')->url($registro->fotografia);
                        }
                    }
                    return $this->crearRespuesta(1,$empleados,200);
                }else{
                    return $this->crearRespuesta(2,"No se han encontrado candidatos",200);
                }
            }else{
                return $this->crearRespuesta(2,"Está empresa no cuenta con clientes configurados",301);
            }
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
    public function crearNuevoEmpleado(Request $request)
    {
        try{
            $fecha = $this->getHoraFechaActual();
            $usuario_creacion = $request["usuario_creacion"];
            $id_cliente = DB::table('nom_sucursales')
            ->where("id_sucursal",$request["id_sucursal"])
            ->first()->id_cliente;
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
                $canditado->id_status = 1;  //Activo
                $canditado->id_cliente = $id_cliente;
                $canditado->id_fotografia = $id_fotografia;
                $canditado->id_direccion = $id_direccion;
                $canditado->nombre = strtoupper($request["candidato"]["nombre"]);
                $canditado->apellido_paterno = strtoupper($request["candidato"]["apellido_paterno"]);
                $canditado->apellido_materno = strtoupper($request["candidato"]["apellido_materno"]);
                $canditado->rfc = $request["candidato"]["rfc"];
                $canditado->curp = $request["candidato"]["curp"];
                $canditado->numero_seguro = $request["candidato"]["numero_social"];
                $canditado->fecha_nacimiento = date("Y-m-d",strtotime($request["candidato"]["fecha_nacimiento"]));
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
                $puesto = $request["id_puesto"];
                $empleado = new Empleado;
                $empleado->id_candidato = $id_candidato;
                $empleado->id_estatus = $request["id_status"];
                $empleado->id_nomina = $request["id_nomina"];
                $empleado->id_puesto = $puesto;
                $empleado->id_sucursal = $request["id_sucursal"];
                $empleado->id_registropatronal = $request["id_registropatronal"];
                $empleado->id_catbanco = $request["id_catbanco"];
                $empleado->id_contratosat = $request["id_contratosat"];
                $empleado->folio = $request["folio"];
                $empleado->fecha_ingreso = date("Y-m-d",strtotime($request["fecha_ingreso"]));
                $empleado->fecha_antiguedad = date("Y-m-d",strtotime($request["fecha_antiguedad"]));
                $empleado->cuenta = $request["cuenta"];
                $empleado->tarjeta = $request["tarjeta"];
                $empleado->clabe = $request["clabe"];
                $empleado->tipo_salario = $request["tipo_salario"];
                $empleado->jornada = $request["jornada"];
                $empleado->sueldo_integrado = $request["sueldo_integrado"];
                $empleado->sueldo_diario = $request["sueldo_diario"];
                $empleado->sueldo_complemento = $request["sueldo_complemento"];
                $empleado->aplicarsueldoneto = $request["aplicarsueldoneto"];
                $empleado->sinsubsidio = $request["sinsubsidio"];
                $empleado->prestaciones_antiguedad = $request["prestaciones_antiguedad"];
                $empleado->usuario_creacion = $usuario_creacion;
                $empleado->fecha_creacion = $fecha;
                $empleado->save();
                //Modificar las vacantes del puesto
                $contratados_actuales = DB::table('gen_cat_puesto')
                ->select("contratados")
                ->where("id_puesto",$puesto)
                ->get();
                $contratos_nuevos = 1;
                if($contratados_actuales[0]->contratados != ""){
                    $contratos_nuevos = intval($contratados_actuales[0]->contratados)+1;
                }
                DB::update('update gen_cat_puesto set contratados = ? where id_puesto = ?', [$contratos_nuevos,$puesto]);
                return $this->crearRespuesta(1,"El empleado se ha creado con éxito",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function modificarEmpleadoAnt(Request $request)
    {
        try{
        $fecha = $this->getHoraFechaActual();
        $usuario_modificacion = $request["usuario_creacion"];
        $id_cliente = DB::table('nom_sucursales')
        ->where("id_sucursal",$request["id_sucursal"])
        ->first()->id_cliente;
        //insertar fotografia
            $id_fotografia = $request["candidato"]["fotografia"]["id_fotografia"];
        //Actualizar fotografia
            if($request["candidato"]["fotografia"]["docB64"] == ""){
                //Guardar foto default
                DB::update('update gen_cat_fotografia set fecha_modificacion = ?, usuario_modificacion = ? where id_fotografia = ?', [$fecha,$usuario_modificacion,$id_fotografia]);
            }else{
                $file = base64_decode($request["candidato"]["fotografia"]["docB64"]);
                $nombre_image = "Cliente".$id_cliente."/candidato_img_".$id_fotografia.".".$id_fotografia;
                if(Storage::disk('candidato')->has($nombre_image)){
                    Storage::disk('candidato')->delete($nombre_image);
                    DB::update('update gen_cat_fotografia set fecha_modificacion = ?, usuario_modificacion = ? where id_fotografia = ?', [$fecha,$usuario_modificacion,$id_fotografia]);
                    Storage::disk('candidato')->put($nombre_image, $file);
                }else{
                    DB::update('update gen_cat_fotografia set nombre = ?, fecha_modificacion = ?, usuario_modificacion = ? where id_fotografia = ?', [$nombre_image,$fecha,$usuario_modificacion,$id_fotografia]);
                    Storage::disk('candidato')->put($nombre_image, $file);
                }
            }
            //Insertar dirección
            $direccion = Direccion::find($request["candidato"]["direccion"]["id_direccion"]);
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
            $direccion->fecha_modificacion = $fecha;
            $direccion->usuario_modificacion = $usuario_modificacion;
            $direccion->activo = 1;
            $direccion->save();
            //Insertar candidato
            $canditado = Candidato::find($request["candidato"]["id_candidato"]);    
            $canditado->id_status = 1;  //Activo
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
            $canditado->fecha_modificacion = $fecha;
            $canditado->usuario_modificacion = $usuario_modificacion;
            $canditado->activo = 1;
            $canditado->save();
            $puesto = $request["id_puesto"];
            //Modificar las vacantes del puesto
            $puesto_anterior = DB::table('nom_empleados as ne')
            ->select("ne.id_puesto")
            ->where("ne.id_empleado",$request["id_empleado"])
            ->get();
            if($puesto != $puesto_anterior[0]->id_puesto){
                //Modificar puesto anterior
                $contratados_actuales = DB::table('gen_cat_puesto')
                ->select("contratados")
                ->where("id_puesto",$puesto_anterior[0]->id_puesto)
                ->get();
                $contratados_viejos = intval($contratados_actuales[0]->contratados)-1;
                DB::update('update gen_cat_puesto set contratados = ? where id_puesto = ?', [$contratados_viejos,$puesto_anterior[0]->id_puesto]);
                //Modificar puesto nuevo
                $contratados_actuales = DB::table('gen_cat_puesto')
                ->select("contratados")
                ->where("id_puesto",$puesto)
                ->get();
                $contratos_nuevos = 1;
                if($contratados_actuales[0]->contratados != ""){
                    $contratos_nuevos = intval($contratados_actuales[0]->contratados)+1;
                }
                DB::update('update gen_cat_puesto set contratados = ? where id_puesto = ?', [$contratos_nuevos,$puesto]);
            }
            //Insertar Empleado
            $empleado = Empleado::find($request["id_empleado"]);
            $empleado->id_estatus = $request["id_status"];
            $empleado->id_puesto = $puesto;
            $empleado->id_sucursal = $request["id_sucursal"];
            $empleado->id_registropatronal = $request["id_registropatronal"];
            $empleado->id_catbanco = $request["id_catbanco"];
            $empleado->id_contratosat = $request["id_contratosat"];
            $empleado->folio = $request["folio"];
            $empleado->fecha_ingreso = date("Y-m-d",strtotime($request["fecha_ingreso"]));
            $empleado->fecha_antiguedad = date("Y-m-d",strtotime($request["fecha_antiguedad"]));
            $empleado->cuenta = $request["cuenta"];
            $empleado->tarjeta = $request["tarjeta"];
            $empleado->clabe = $request["clabe"];
            $empleado->tipo_salario = $request["tipo_salario"];
            $empleado->jornada = $request["jornada"];
            $empleado->sueldo_integrado = $request["sueldo_integrado"];
            $empleado->sueldo_diario = $request["sueldo_diario"];
            $empleado->sueldo_complemento = $request["sueldo_complemento"];
            $empleado->aplicarsueldoneto = $request["aplicarsueldoneto"];
            $empleado->sinsubsidio = $request["sinsubsidio"];
            $empleado->prestaciones_antiguedad = $request["prestaciones_antiguedad"];
            $empleado->usuario_modificacion = $usuario_modificacion;
            $empleado->fecha_modificacion = $fecha;
            $empleado->save();
            return $this->crearRespuesta(1,"El empleado se ha creado con éxito",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function cargaEmpleado(Request $res)
    {
        try{
            $nombre = $this->trataPalabra($res["nombre"]." ".$res["apellido_paterno"]." ".$res["apellido_materno"]);
            $curp = $this->trataPalabra($res["curp"]);
            $rfc = $this->trataPalabra($res["rfc"]);
            if(isset($res["tipo"])){
                $validar_existencia = DB::table('rh_cat_candidato as rcc')
                ->select("id_candidato","id_direccion")
                ->orwhere(DB::raw("CONCAT(rcc.nombre,' ',rcc.apellido_paterno,' ',rcc.apellido_materno)"),$nombre)
                ->orWhere("rcc.rfc",$rfc)
                ->orWhere("rcc.curp",$curp)
                ->get();
                if($res["tipo"] == 1){  //ALTAS Y MODIFICACIONES
                    if(count($validar_existencia)>0){
                        //RECUPERAR ID'S
                        $id_empresa = $this->obtenerIdPorNombre("gen_cat_empresa","empresa",$this->trataPalabra($res["empresa"]),"id_empresa");
                        $id_sucursal = $this->obtenerIdPorNombre("nom_sucursales","sucursal",$this->trataPalabra($res["sucursal"]),"id_sucursal");
                        $id_nomina = $this->obtenerIdPorNombre("nom_cat_nomina","nomina",$this->trataPalabra($res["nomina"]),"id_nomina");
                        $id_puesto_departamento = $this->obtenerIdPorNombre("gen_cat_puesto","puesto",$this->trataPalabra($res["puesto"]),["id_puesto","id_departamento"]);
                        //SE ACTUALIZA EL CANDIDATO Y SU DIRECCIÓN
                        DB::update('update rh_cat_candidato set nombre = ?, apellido_paterno = ?, apellido_materno = ?, rfc = ?, curp = ?, numero_seguro = ?, telefono = ? where id_candidato = ?', [$this->trataPalabra($res["nombre"]),$this->trataPalabra($res["apellido_paterno"]),$this->trataPalabra($res["apellido_materno"]),$this->trataPalabra($res["rfc"]),$this->trataPalabra($res["curp"]),$this->trataPalabra($res["numero_seguro"]),$res["telefono"],$validar_existencia[0]->id_candidato]);
                        DB::update('update gen_cat_direccion set calle = ?, numero_interior = ?, numero_exterior = ?, cruzamiento_uno = ?, cruzamiento_dos = ?, colonia = ?, municipio = ?, estado = ?, codigo_postal = ? where id_direccion = ?', [$this->trataPalabra($res["calle"]),$this->trataPalabra($res["numero_interior"]),$this->trataPalabra($res["numero_exterior"]),$this->trataPalabra($res["cruzamiento_uno"]),$this->trataPalabra($res["cruzamiento_dos"]),$this->trataPalabra($res["colonia"]),$this->trataPalabra($res["municipio"]),$this->trataPalabra($res["estado"]),$res["codigo_postal"],$validar_existencia[0]->id_direccion]);
                        //VALIDAR SI EL CANDIDATO EXISTE COMO EMPLEADO
                        $es_empleado = DB::table('nom_empleados')
                        ->select("id_empleado")
                        ->where("id_candidato",$validar_existencia[0]->id_candidato)
                        ->get();
                        if(count($es_empleado)>0){
                            //EL CANDIDATO EXISTE Y ES EMPLEADO SOLO SE ACTUALIZA
                            DB::update('update nom_empleados set id_sucursal = ?, id_registropatronal = ?, id_nomina = ?, fecha_ingreso = ?, id_puesto = ?, cuenta = ?, sueldo_diario = ?, sueldo_integrado = ?, sueldo_complemento = ?  where id_empleado = ?', [$id_sucursal->id_sucursal,1,$id_nomina->id_nomina,date("Y-m-d",strtotime($res["fecha_ingreso"])),$id_puesto_departamento->id_puesto,$res["cuenta"],$res["sueldo_diario"],$res["sueldo_integrado"],$res["sueldo_complemento"],$es_empleado[0]->id_empleado]);
                            
                        }else{
                            //EL CANDIDATO EXISTE PERO NO COMO EMPLEADO SE INSERTA
                            DB::insert('insert into nom_empleados (id_candidato, id_estatus, id_nomina, id_puesto, id_sucursal, id_registropatronal, fecha_ingreso, cuenta, sueldo_diario, sueldo_integrado, sueldo_complemento, usuario_creacion, fecha_creacion) values (?,?,?,?,?,?,?,?,?,?,?,?,?)', [$validar_existencia[0]->id_candidato,1,$id_nomina->id_nomina,$id_puesto_departamento->id_puesto,$id_sucursal->id_sucursal,1,date("Y-m-d",strtotime($res["fecha_ingreso"])),$res["cuenta"],$res["sueldo_diario"],$res["sueldo_integrado"],$res["sueldo_complemento"],$res["usuario"],$this->getHoraFechaActual()]);
                        }
                        return $this->crearRespuesta(1,"Se ha completado con exito",200);
                    }else{
                        //NO EXISTE EL CANDIDATO, SE INSERTA EL CANDIDATO LA DIRECCIÓN Y EL EMPLEADO
                        $id_empresa = $this->obtenerIdPorNombre("gen_cat_empresa","empresa",$this->trataPalabra($res["empresa"]),"id_empresa");
                        $id_sucursal = $this->obtenerIdPorNombre("nom_sucursales","sucursal",$this->trataPalabra($res["sucursal"]),"id_sucursal");
                        //Obtener id_cliente
                        $id_cliente = $this->obtenerIdPorNombre("gen_cat_cliente","cliente",$this->trataPalabra($res["cliente"]),"id_cliente");
                        //Insertar fotografia
                        $id_fotografia = $this->getSigId("gen_cat_fotografia");
                        DB::insert('insert into gen_cat_fotografia (id_fotografia, nombre, fecha_creacion, usuario_creacion, activo) values (?,?,?,?,?)', [$id_fotografia,"candidato_default.svg",$this->getHoraFechaActual(),$res["usuario"],1]);
                        //Insertar dirección
                        $id_direccion = $this->getSigId("gen_cat_direccion");
                        $direccion = new Direccion;
                        $direccion->id_direccion = $id_direccion;
                        $direccion->calle = $this->trataPalabra($res["calle"]);
                        $direccion->numero_interior = $this->trataPalabra($res["numero_interior"]);
                        $direccion->numero_exterior = $this->trataPalabra($res["numero_exterior"]);
                        $direccion->cruzamiento_uno = $this->trataPalabra($res["cruzamiento_uno"]);
                        $direccion->cruzamiento_dos = $this->trataPalabra($res["cruzamiento_dos"]);
                        $direccion->codigo_postal = $this->trataPalabra($res["codigo_postal"]);
                        $direccion->colonia = $this->trataPalabra($res["colonia"]);
                        $direccion->municipio = $this->trataPalabra($res["municipio"]);
                        $direccion->estado = $this->trataPalabra($res["estado"]);
                        $direccion->fecha_creacion = $this->getHoraFechaActual();
                        $direccion->usuario_creacion = $res["usuario"];
                        $direccion->activo = 1;
                        $direccion->save();
                        //Insertar candidato
                        $id_candidato = $this->getSigId("rh_cat_candidato");
                        $canditado = new Candidato;
                        $canditado->id_candidato = $id_candidato;
                        $canditado->id_status = 1;  //Activo
                        $canditado->id_cliente = $id_cliente->id_cliente;
                        $canditado->id_fotografia = $id_fotografia;
                        $canditado->id_direccion = $id_direccion;
                        $canditado->nombre = $this->trataPalabra($res["nombre"]);
                        $canditado->apellido_paterno = $this->trataPalabra($res["apellido_paterno"]);
                        $canditado->apellido_materno = $this->trataPalabra($res["apellido_materno"]);
                        $canditado->rfc = $this->trataPalabra($res["rfc"]);
                        $canditado->curp = $this->trataPalabra($res["curp"]);
                        $canditado->numero_seguro = $this->trataPalabra($res["numero_social"]);
                        $canditado->telefono = $this->trataPalabra($res["telefono"]);
                        $canditado->fecha_creacion = $this->getHoraFechaActual();
                        $canditado->usuario_creacion = $res["usuario"];
                        $canditado->activo = 1;
                        $canditado->save();
                        //Insertar Empleado
                        $id_puesto_departamento = $this->obtenerIdPorNombre("gen_cat_puesto","puesto",$this->trataPalabra($res["puesto"]),["id_puesto","id_departamento"]);
                        //Aumentar vacantes del puesto
                        $contratados = DB::table('gen_cat_puesto')
                        ->select("contratados")
                        ->where("id_puesto",$id_puesto_departamento->id_puesto)
                        ->first()->contratados;
                        DB::update('update gen_cat_puesto set contratados = ? where id_puesto = ?', [(intval($contratados)+1),$id_puesto_departamento->id_puesto]);
                        $id_nomina = $this->obtenerIdPorNombre("nom_cat_nomina","nomina",$this->trataPalabra($res["nomina"]),"id_nomina");
                        $puesto = $id_puesto_departamento->id_puesto;
                        $empleado = new Empleado;
                        $empleado->id_candidato = $id_candidato;
                        $empleado->id_estatus = 1;
                        $empleado->id_nomina = $id_nomina->id_nomina;
                        $empleado->id_puesto = $puesto;
                        $empleado->id_sucursal = $id_sucursal->id_sucursal;
                        $empleado->id_registropatronal = 1;
                        $empleado->fecha_antiguedad = date("Y-m-d",strtotime($res["fecha_antiguedad"]));
                        $empleado->cuenta = $res["cuenta"];
                        $empleado->sueldo_integrado = $res["sueldo_integrado"];
                        $empleado->sueldo_diario = $res["sueldo_diario"];
                        $empleado->sueldo_complemento = $res["sueldo_complemento"];
                        $empleado->usuario_creacion = $res["usuario"];
                        $empleado->fecha_creacion = $this->getHoraFechaActual();
                        $empleado->save();
                        return $this->crearRespuesta(1,"Empleado agregado",200);
                    }
                }
                if($res["tipo"] == 2){  //BAJAS

                }
            }else{
                return $this->crearRespuesta(2,"No se ha indentificado el tipo de importación",200);
            }
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),200);
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
    public function obtenerIdPorNombre($nombre_tabla,$columna_name,$data,$id_name)
    {
        $id = DB::table($nombre_tabla)
        ->select($id_name)
        ->where($columna_name,$data)
        ->first();
        if($id){
            return $id;
        }
    }
}
