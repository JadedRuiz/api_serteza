<?php

namespace App\Http\Controllers;

use App\Models\Candidato;
use App\Models\Direccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CandidatoController extends Controller
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
    public function obtenerDatosDashBoard(){
        $usuario_totales = count(Candidato::where("activo",1)->get());
        $usuarios_contratatos = count(Candidato::where("gen_gen_cat_status_id",1)->where("activo",1)->get());
        $usuarios_por_contratar = count(Candidato::where("gen_gen_cat_status_id",6)->where("activo",1)->get());
        $numero_solicitudes = 0; //Aun no se como sacarlo
        $respuesta = [
            "num_solicitudes" => $numero_solicitudes,
            "por_contratar" => $usuarios_por_contratar,
            "contratados" => $usuarios_contratatos,
            "total" => $usuario_totales
        ];
        return $this->crearRespuesta(1,$respuesta,200);
    }
    public function obtenerCandidatos(Request $res){
        $take = $res["taken"];
        $pagina = $res["pagina"];
        $status = $res["status"];
        $palabra = $res["palabra"];
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
        $incia = intval($pagina) * intval($take);
        $registros = DB::table('rh_cat_candidato as cc')
        ->select("cc.nombre","cc.apellido_paterno","cc.id_candidato","cs.status","cc.apellido_materno","cc.activo","cf.nombre as fotografia")
        ->join("gen_cat_statu as cs","cs.id_statu","=","cc.id_status")
        ->join("gen_cat_fotografia as cf","cf.id_fotografia","=","cc.id_fotografia")
        ->where("cc.id_cliente",$id_cliente)
        ->where("cc.activo",1)
        ->where("cc.id_status",$otro,$status)
        ->where(function ($query) use ($otro_dos,$palabra){
            $query->where("cc.nombre",$otro_dos,$palabra)
                  ->orWhere("cc.apellido_paterno",$otro_dos,$palabra)
                  ->orWhere("cc.apellido_materno",$otro_dos,$palabra);
        })
        ->skip($incia)
        ->take($take)
        ->get();
        if(isset($res["tipo"]) && $res["tipo"] == 1){
            foreach($registros as $registro){
                $registro->fotografia = Storage::disk('candidato')->url($registro->fotografia);
            }
        }
        $contar = DB::table('rh_cat_candidato as cc')
        ->where("cc.id_cliente",$id_cliente)
        ->where("cc.activo",1)
        ->where("cc.id_status",$otro,$status)
        ->where(function ($query) use ($otro_dos,$palabra){
            $query->where("cc.nombre",$otro_dos,$palabra)
                  ->orWhere("cc.apellido_paterno",$otro_dos,$palabra)
                  ->orWhere("cc.apellido_materno",$otro_dos,$palabra);
        })
        ->get();
        if(count($registros)>0){
            $respuesta = [
                "total" => count($contar),
                "registros" => $registros
            ];
            return $this->crearRespuesta(1,$respuesta,200);
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
        ->select("rcc.id_candidato", "rcc.id_fotografia", "rcc.id_status", "rcc.apellido_paterno", "rcc.apellido_materno", "rcc.nombre", "rcc.rfc", "rcc.curp", "rcc.numero_seguro", "rcc.edad", "rcc.fecha_nacimiento", "rcc.correo", "rcc.telefono", "rcc.telefono_dos", "rcc.telefono_tres", "rcc.descripcion","gcd.id_direccion","gcd.calle", "gcd.numero_interior", "gcd.numero_exterior", "gcd.cruzamiento_uno", "gcd.cruzamiento_dos", "gcd.codigo_postal", "gcd.colonia", "gcd.localidad", "gcd.municipio", "gcd.estado", "gcd.descripcion as descripcion_direccion","gce.status","cf.nombre as fotografia")
        ->join("cat_direccion as gcd","gcd.id_direccion","=","rcc.id_direccion")
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
        try{
        $fecha = $this->getHoraFechaActual();
        $usuario_creacion = $request["usuario_creacion"];
        //insertar fotografia
        $id_fotografia = $this->getSigId("gen_cat_fotografia");
        //Insertar fotografia
        if($request["fotografia"]["docB64"] == ""){
            //Guardar foto default
            DB::insert('insert into gen_cat_fotografia (id_fotografia, nombre, fecha_creacion, usuario_creacion, activo) values (?,?,?,?,?)', [$id_fotografia,"candidato_default.svg",$fecha,$usuario_creacion,1]);
        }else{
            $file = base64_decode($request["fotografia"]["docB64"]);
            $nombre_image = "candidato_img_".$id_fotografia.".".$request["fotografia"]["extension"];
            DB::insert('insert into gen_cat_fotografia (id_fotografia, nombre, fecha_creacion, usuario_creacion, activo) values (?,?,?,?,?)', [$id_fotografia,$nombre_image,$fecha,$usuario_creacion,1]);
            Storage::disk('candidato')->put($nombre_image, $file);
        }
        //Insertar dirección
            $id_direccion = $this->getSigId("gen_cat_direccion");
            $direccion = new Direccion;
            $direccion->id_direccion = $id_direccion;
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
            $direccion->fecha_creacion = $fecha;
            $direccion->usuario_creacion = $usuario_creacion;
            $direccion->activo = 1;
            $direccion->save();
        //Insertar candidato
        
            $canditado = new Candidato;
            $canditado->id_candidato = $this->getSigId("rh_cat_candidato");
            $canditado->id_status = $request["id_statu"];  //En reclutamiento
            $canditado->id_cliente = $request["id_cliente"];
            $canditado->id_fotografia = $id_fotografia;
            $canditado->id_direccion = $id_direccion;
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
            $fecha = $this->getHoraFechaActual();
            $usuario_creacion = $request["usuario_creacion"];
            //Actualizar candidato
            $canditado = Candidato::find($request["id_candidato"]);
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
            $canditado->usuario_modificacion = $usuario_creacion;
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
            $direccion->usuario_modificacion = $usuario_creacion;
            $direccion->save();
            //Actualizar foto
            DB::update('update gen_gen_cat_fotografia set fotografia = ?, extension = ? where id_fotografia = ?',[$request["fotografia"]["docB64"],$request["fotografia"]["extension"],$request["fotografia"]["id_fotografia"]]);
            return $this->crearRespuesta(1,"Se ha actualizado con exito",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
}
