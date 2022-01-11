<?php

namespace App\Http\Controllers;

use App\Models\Candidato;
use App\Models\Direccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CandidatoController extends Controller
{
    public function autoComplete(Request $res){
        $status = $res["status"];
        $palabra = "%".strtoupper($res["nombre_candidato"])."%";
        $id_cliente = $res["id_cliente"];
        if($status == "-1"){
            $otro = "!=";
            $status = -1;
        }else{
            $otro = "=";
            $status = intval($status);
        }
        $busqueda = Candidato::select(DB::raw('CONCAT(rh_cat_candidato.apellido_paterno, " ", rh_cat_candidato.apellido_materno, " ", rh_cat_candidato.nombre) as nombre_completo'),"rh_cat_candidato.id_candidato","rh_cat_candidato.id_status","rh_cat_candidato.activo","cf.nombre as fotografia", "rh_cat_candidato.apellido_paterno","rh_cat_candidato.apellido_materno", "rh_cat_candidato.nombre","rh_cat_candidato.descripcion","cs.status")
        ->join("gen_cat_statu as cs","cs.id_statu","=","rh_cat_candidato.id_status")
        ->join("gen_cat_fotografia as cf","cf.id_fotografia","=","rh_cat_candidato.id_fotografia")
        ->where("id_cliente",$id_cliente)
        ->where("rh_cat_candidato.activo",1)
        ->where(function($query) use ($palabra){
            $query->where(DB::raw('CONCAT(apellido_paterno, " ", apellido_materno, " ", rh_cat_candidato.nombre)'),"like",$palabra)
            ->orWhere('rh_cat_candidato.descripcion','LIKE',$palabra);
        })
        ->where("rh_cat_candidato.id_status",$otro,$status)
        ->take(10)
        ->get();
        if(count($busqueda)>0){
            foreach ($busqueda as $canditado) {
                $canditado->fotografia = Storage::disk('candidato')->url($canditado->fotografia);
                $canditado->nombre = $canditado->apellido_paterno." ".$canditado->apellido_materno." ".$canditado->nombre;
            }
            return $this->crearRespuesta(1,$busqueda,200);
        }
        return $this->crearRespuesta(2,"No se han encontrado resultados",200);
    }
    public function obtenerCandidatos(Request $res){
        $status = $res["status"];
        $palabra = strtoupper($res["palabra"]);
        $id_cliente = $res["id_cliente"];
        $otro = "";
        if($status == "-1"){
            $otro = "!=";
            $status = -1;
        }else{
            $otro = "=";
            $status = intval($status);
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
        $registros = DB::table('rh_cat_candidato as cc')
        ->select(DB::raw('CONCAT(cc.apellido_paterno, " ", cc.apellido_materno, " ", cc.nombre) as nombre_completo'),"cc.id_candidato","cs.status","cc.activo","cf.nombre as fotografia", "cc.apellido_paterno","cc.apellido_materno", "cc.nombre","cc.descripcion","cs.id_statu")
        ->join("gen_cat_statu as cs","cs.id_statu","=","cc.id_status")
        ->join("gen_cat_fotografia as cf","cf.id_fotografia","=","cc.id_fotografia")
        ->where("cc.id_cliente",$id_cliente)
        ->where("cc.activo",1)
        ->where("cc.id_status",$otro,$status)
        ->where(DB::raw('CONCAT(cc.apellido_paterno, " ", cc.apellido_materno, " ", cc.nombre)'),$otro_dos,$palabra)
        ->orderBy("cc.fecha_creacion","desc")
        ->take(1000)
        ->get();
        if(isset($res["tipo"]) && $res["tipo"] == 1){
            foreach($registros as $registro){
                $registro->fotografia = Storage::disk('candidato')->url($registro->fotografia);
            }
        }
        if(count($registros)>0){
            return $this->crearRespuesta(1,$registros,200);
        }else{
            return $this->crearRespuesta(2,"No hay candidatos que mostrar",200);
        }
    }
    public function obtenerCandidatosPorIdCliente($id){
        $validador = Candidato::where("gen_cat_clientes_id",$id)
        ->select("nombre","apellido_paterno","apellido_materno","id")
        ->where("activo",1)
        ->orderBy("apellido_paterno","ASC")
        ->get();
        if(count($validador)>0){
            foreach($validador as $canditado){
                $canditado->nombre = $canditado->apellido_paterno." ".$canditado->apellido_materno." ".$canditado->nombre;
            }
            return $this->crearRespuesta(1,$validador,200);
        }else{
            return $this->crearRespuesta(2,"No se han encontrado candidatos",200);
        }
    }
    public function obtenerCandidatoPorId($id){
        $respuesta = DB::table("rh_cat_candidato as rcc")
        ->select("rcc.id_candidato", "rcc.id_fotografia", "rcc.id_status", "rcc.apellido_paterno", "rcc.apellido_materno", "rcc.nombre", "rcc.rfc", "rcc.curp", "rcc.numero_seguro", "rcc.edad", "rcc.fecha_nacimiento", "rcc.correo", "rcc.telefono", "rcc.telefono_dos", "rcc.telefono_tres", "rcc.descripcion","gcd.id_direccion","gcd.calle", "gcd.numero_interior", "gcd.numero_exterior", "gcd.cruzamiento_uno", "gcd.cruzamiento_dos", "gcd.codigo_postal", "gcd.colonia", "gcd.localidad", "gcd.municipio", "gcd.estado", "gcd.descripcion as descripcion_direccion","gce.status","cf.nombre as fotografia","rcc.id_cliente")
        ->join("gen_cat_direccion as gcd","gcd.id_direccion","=","rcc.id_direccion")
        ->join("gen_cat_statu as gce","gce.id_statu","=","rcc.id_status")
        ->join("gen_cat_fotografia as cf","cf.id_fotografia","=","rcc.id_fotografia")
        ->where("rcc.id_candidato",$id)
        ->where("rcc.activo",1)
        ->get();
        if(count($respuesta)>0){
            $respuesta[0]->fotografia = Storage::disk('candidato')->url($respuesta[0]->fotografia);
            return $this->crearRespuesta(1,$respuesta,200);
        }else{
            return $this->crearRespuesta(2,"El usuario no ha sido encontrado",200);
        }
        
    }
    public function altaCandidato(Request $request){
        //validate incoming request 
        $this->validate($request, [
            'nombre' => 'required|string|max:150',
            'apellido_paterno' => 'required|max:150',
            'apellido_materno' => 'required|max:150',
        ]);
        $validar_rfc = DB::table('rh_cat_candidato')
        ->where("rfc",$request["rfc"])
        ->where("activo",1)
        ->get();
        if(count($validar_rfc)>0){
            return $this->crearRespuesta(2,"El RFC ya se encuentra registrado",301);
        }
        $validar_curp = DB::table('rh_cat_candidato')
        ->where("curp",$request["curp"])
        ->where("activo",1)
        ->get();
        if(count($validar_curp)>0){
            return $this->crearRespuesta(2,"El CURP ya se encuentra registrado",301);
        }
        try{
        $fecha = $this->getHoraFechaActual();
        $usuario_creacion = $request["usuario_creacion"];
        $id_cliente = $request["id_cliente"];
        //insertar fotografia
        $id_fotografia = $this->getSigId("gen_cat_fotografia");
        //Insertar fotografia
        if($request["fotografia"]["docB64"] == ""){
            //Guardar foto default
            DB::insert('insert into gen_cat_fotografia (id_fotografia, nombre, fecha_creacion, usuario_creacion, activo) values (?,?,?,?,?)', [$id_fotografia,"candidato_default.svg",$fecha,$usuario_creacion,1]);
        }else{
            $file = base64_decode($request["fotografia"]["docB64"]);
            $nombre_image = "Cliente".+$id_cliente."/candidato_img_".$id_fotografia.".".$request["fotografia"]["extension"];
            DB::insert('insert into gen_cat_fotografia (id_fotografia, nombre, fecha_creacion, usuario_creacion, activo) values (?,?,?,?,?)', [$id_fotografia,$nombre_image,$fecha,$usuario_creacion,1]);
            Storage::disk('candidato')->put($nombre_image, $file);
        }
        //Insertar dirección
            $id_direccion = $this->getSigId("gen_cat_direccion");
            $direccion = new Direccion;
            $direccion->id_direccion = $id_direccion;
            $direccion->calle =strtoupper($request["direccion"]["calle"]);
            $direccion->numero_interior = strtoupper($request["direccion"]["numero_interior"]);
            $direccion->numero_exterior = strtoupper($request["direccion"]["numero_exterior"]);
            $direccion->cruzamiento_uno = strtoupper($request["direccion"]["cruzamiento_uno"]);
            $direccion->cruzamiento_dos = strtoupper($request["direccion"]["cruzamiento_dos"]);
            $direccion->codigo_postal = $request["direccion"]["codigo_postal"];
            $direccion->colonia = strtoupper($request["direccion"]["colonia"]);
            $direccion->localidad = strtoupper($request["direccion"]["localidad"]);
            $direccion->municipio = strtoupper($request["direccion"]["municipio"]);
            $direccion->estado = strtoupper($request["direccion"]["estado"]);
            $direccion->descripcion = strtoupper($request["direccion"]["descripcion"]);
            $direccion->fecha_creacion = $fecha;
            $direccion->usuario_creacion = $usuario_creacion;
            $direccion->activo = 1;
            $direccion->save();
        //Insertar candidato
        
            $canditado = new Candidato;
            $canditado->id_candidato = $this->getSigId("rh_cat_candidato");
            $canditado->id_status = $request["id_statu"];  //En reclutamiento
            $canditado->id_cliente = $id_cliente;
            $canditado->id_fotografia = $id_fotografia;
            $canditado->id_direccion = $id_direccion;
            $canditado->nombre = strtoupper($request["nombre"]);
            $canditado->apellido_paterno = strtoupper($request["apellido_paterno"]);
            $canditado->apellido_materno = strtoupper($request["apellido_materno"]);
            $canditado->rfc = strtoupper($request["rfc"]);
            $canditado->curp = strtoupper($request["curp"]);
            $canditado->numero_seguro = $request["numero_social"];
            $canditado->fecha_nacimiento = $request["fecha_nacimiento"];
            $canditado->correo = $request["correo"];
            $canditado->telefono =$request["telefono"];
            $canditado->edad = $request["edad"];
            $canditado->telefono_dos =$request["telefono_dos"];
            $canditado->telefono_tres =$request["telefono_tres"];
            $canditado->descripcion = strtoupper($request["descripcion"]);
            $canditado->fecha_creacion = $fecha;
            $canditado->usuario_creacion = $usuario_creacion;
            $canditado->activo = 1;
            $canditado->save();
            return $this->crearRespuesta(1,"El candidato ha sido registrado correctamente",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function eliminarCandidato($id){
        $data = DB::update('update rh_cat_candidato set activo = 0 where id_candidato = ?',[$id]);
        return $this->crearRespuesta(1,"Candidato Eliminado",200);
    }
    public function actualizarCandidato(Request $request){
        try{
            $canditado = Candidato::find($request["id_candidato"]);
            $fecha = $this->getHoraFechaActual();
            $usuario_modificacion = $request["usuario_creacion"];
            $id_fotografia = $request["fotografia"]["id_fotografia"];
            //Actualizar fotografia
            if($request["fotografia"]["docB64"] == ""){
                //Guardar foto default
                DB::update('update gen_cat_fotografia set fecha_modificacion = ?, usuario_modificacion = ? where id_fotografia = ?', [$fecha,$usuario_modificacion,$id_fotografia]);
            }else{
                $file = base64_decode($request["fotografia"]["docB64"]);
                $nombre_image = "Cliente".$canditado->id_cliente."/candidato_img_".$id_fotografia.".".$request["fotografia"]["extension"];
                if(Storage::disk('candidato')->has($nombre_image)){
                    Storage::disk('candidato')->delete($nombre_image);
                    DB::update('update gen_cat_fotografia set fecha_modificacion = ?, usuario_modificacion = ? where id_fotografia = ?', [$fecha,$usuario_modificacion,$request["fotografia"]["id_fotografia"]]);
                    Storage::disk('candidato')->put($nombre_image, $file);
                }else{
                    DB::update('update gen_cat_fotografia set nombre = ?, fecha_modificacion = ?, usuario_modificacion = ? where id_fotografia = ?', [$nombre_image,$fecha,$usuario_modificacion,$request["fotografia"]["id_fotografia"]]);
                    Storage::disk('candidato')->put($nombre_image, $file);
                }
            }
            //Actualizar candidato
            $canditado->id_status = $request["id_statu"];  //En reclutamiento
            $canditado->nombre = strtoupper($request["nombre"]);
            $canditado->apellido_paterno = strtoupper($request["apellido_paterno"]);
            $canditado->apellido_materno = strtoupper($request["apellido_materno"]);
            $canditado->rfc = $request["rfc"];
            $canditado->curp = $request["curp"];
            $canditado->numero_seguro = $request["numero_social"];
            $canditado->fecha_nacimiento = $request["fecha_nacimiento"];
            $canditado->correo = $request["correo"];
            $canditado->telefono =$request["telefono"];
            $canditado->edad = $request["edad"];
            $canditado->telefono_dos =$request["telefono_dos"];
            $canditado->telefono_tres =$request["telefono_tres"];
            $canditado->descripcion = $request["descripcion"];
            $canditado->fecha_modificacion = $fecha;
            $canditado->usuario_modificacion = $usuario_modificacion;
            $canditado->activo = 1;
            $canditado->save();
            //Actualizar direccion
            $direccion = Direccion::find($request["direccion"]["id_direccion"]);
            $direccion->calle = $request["direccion"]["calle"];
            $direccion->numero_interior = $request["direccion"]["numero_interior"];
            $direccion->numero_exterior = $request["direccion"]["numero_exterior"];
            $direccion->cruzamiento_uno = $request["direccion"]["cruzamiento_uno"];
            $direccion->cruzamiento_dos = $request["direccion"]["cruzamiento_dos"];
            $direccion->codigo_postal = $request["direccion"]["codigo_postal"];
            $direccion->colonia = $request["direccion"]["colonia"];
            $direccion->localidad = $request["direccion"]["localidad"];
            $direccion->municipio = $request["direccion"]["municipio"];
            $direccion->estado = $request["direccion"]["estado"];
            $direccion->descripcion = $request["direccion"]["descripcion"];
            $direccion->fecha_modificacion = $fecha;
            $direccion->usuario_modificacion = $usuario_modificacion;
            $direccion->save();
            return $this->crearRespuesta(1,"El candidato se ha editado con éxito",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }

    public function obtenerCandidatoActivoId($id_candidato)
    {
        $canditado_activo = DB::table('rh_detalle_contratacion as rdc')
        ->select(DB::raw('CONCAT(rcc.apellido_paterno, " ", rcc.apellido_materno, " ", rcc.nombre) as nombre'),"gcd.departamento","gcp.puesto","rdc.fecha_alta","ncn.nomina","rdc.sueldo","rdc.id_nomina","rdc.id_empresa","rdc.id_departamento","rdc.id_puesto")
        ->join("nom_cat_nomina as ncn","ncn.id_nomina","=","rdc.id_nomina")
        ->join("rh_cat_candidato as rcc","rcc.id_candidato","=","rdc.id_candidato")
        ->join("gen_cat_departamento as gcd","gcd.id_departamento","=","rdc.id_departamento")
        ->join("gen_cat_puesto as gcp","gcp.id_puesto","=","rdc.id_puesto")
        ->where("rdc.id_candidato",$id_candidato)
        ->where("rdc.activo",1)
        ->get();
        if(count($canditado_activo)>0){
            $canditado_activo[0]->fecha_alta = date("Y-m-d",strtotime($canditado_activo[0]->fecha_alta));
            return $this->crearRespuesta(1,$canditado_activo,200);
        }else{
            return $this->crearRespuesta(2,"No se ha encontrado el candidato",301);
        }
    }
    public function obtenerMovientosCandidato($id_candidato)
    {
        $status_candidato = DB::table('rh_cat_candidato as rcc')
        ->select("id_status")
        ->where("rcc.id_candidato",$id_candidato)
        ->first();
        $informacion_contrato_baja = [];
        if($status_candidato->id_status == 2){  //Candidato dato de baja
            $informacion_contrato_baja = DB::table('rh_movimientos as rm')
            ->select("gce.empresa","gcd.departamento","gcp.puesto","ncn.nomina",DB::raw('DATE_FORMAT(rdb.fecha_baja, "%Y-%m-%d") as fecha_alta'),"rm.fecha_movimiento")
            ->join("rh_detalle_baja as rdb","rdb.id_movimiento","=","rm.id_movimiento")
            ->join("gen_cat_empresa as gce","gce.id_empresa","=","rdb.id_empresa")
            ->join("gen_cat_departamento as gcd","gcd.id_departamento","=","rdb.id_departamento")
            ->join("gen_cat_puesto as gcp","gcp.id_puesto","=","rdb.id_puesto")
            ->join("nom_cat_nomina as ncn","ncn.id_nomina","=","rdb.id_nomina")
            ->where("rm.id_status",1)
            ->where("rdb.id_candidato",$id_candidato)
            ->get();
        }
        $informacion_contrato = DB::table('rh_movimientos as rm')
        ->select("gce.empresa","gcd.departamento","gcp.puesto","ncn.nomina","rdc.sueldo",DB::raw('DATE_FORMAT(rdc.fecha_alta, "%Y-%m-%d") as fecha_alta'),"rm.fecha_movimiento")
        ->join("rh_detalle_contratacion as rdc","rdc.id_movimiento","=","rm.id_movimiento")
        ->join("gen_cat_empresa as gce","gce.id_empresa","=","rdc.id_empresa")
        ->join("gen_cat_departamento as gcd","gcd.id_departamento","=","rdc.id_departamento")
        ->join("gen_cat_puesto as gcp","gcp.id_puesto","=","rdc.id_puesto")
        ->join("nom_cat_nomina as ncn","ncn.id_nomina","=","rdc.id_nomina")
        ->where("rm.id_status",1)
        ->where("rdc.id_candidato",$id_candidato)
        ->get();
        $informacion_contrato_actual = DB::table('rh_movimientos as rm')->select("gce.empresa","gcd.departamento","gcp.puesto","ncn.nomina","rdm.sueldo",DB::raw('DATE_FORMAT(rdm.fecha_de_modificacion, "%Y-%m-%d") as fecha_alta'),"rm.fecha_movimiento")
        ->join("rh_detalle_modificacion as rdm","rdm.id_movimiento","=","rm.id_movimiento")
        ->join("gen_cat_empresa as gce","gce.id_empresa","=","rdm.id_empresa")
        ->join("gen_cat_departamento as gcd","gcd.id_departamento","=","rdm.id_departamento")
        ->join("gen_cat_puesto as gcp","gcp.id_puesto","=","rdm.id_puesto")
        ->join("nom_cat_nomina as ncn","ncn.id_nomina","=","rdm.id_nomina")
        ->where("rm.id_status",1)
        ->where("rdm.id_candidato",$id_candidato)
        ->where("rm.activo",1)
        ->orderBy("rm.fecha_movimiento","DESC")
        ->get();
        if(count($informacion_contrato_actual)>0){  //El candidato tiene modificaciones
            $respuesta = [
                "informacion_contrato" => $informacion_contrato_actual[0],
                "movimientos" => [
                    "alta" => $informacion_contrato,
                    "modificaciones" => $informacion_contrato_actual,
                    "baja" => $informacion_contrato_baja
                ]
            ];
        }else{
            $respuesta = [
                "informacion_contrato" => $informacion_contrato,
                "movimientos" => [
                    "alta" => $informacion_contrato,
                    "modificaciones" => $informacion_contrato_actual,
                    "baja" => $informacion_contrato_baja
                ]
            ];
        }
        return $this->crearRespuesta(1,$respuesta,200);
    }
}
