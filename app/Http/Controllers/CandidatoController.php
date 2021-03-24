<?php

namespace App\Http\Controllers;

use App\Models\Candidato;
use App\Models\Direccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $usuarios_contratatos = count(Candidato::where("cat_status_id",1)->where("activo",1)->get());
        $usuarios_por_contratar = count(Candidato::where("cat_status_id",6)->where("activo",1)->get());
        $numero_solicitudes = 0; //Aun no se como sacarlo
        $respuesta = [
            "num_solicitudes" => $numero_solicitudes,
            "por_contratar" => $usuarios_por_contratar,
            "contratados" => $usuarios_contratatos,
            "total" => $usuario_totales
        ];
        return $this->crearRespuesta(1,$respuesta,200);
    }
    public function obtenerCandidatos(){
        return Candidato::all();
    }
    public function obtenerCandidatosPorIdCliente($id){
        $validador = Candidato::where("cat_clientes_id",$id)
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
            return $this->crearRespuesta(2,"Ha ocurrido un error",301);
        }
    }
    public function obtenerCandidatoPorId($id){
        $respuesta = DB::table("rh_cat_candidatos as rcc")
        ->select("rcc.id", "rcc.cat_fotografia_id", "rcc.apellido_paterno", "rcc.apellido_materno", "rcc.nombre", "rcc.rfc", "rcc.curp", "rcc.numero_seguro", "rcc.fecha_nacimiento", "rcc.correo", "rcc.telefono", "rcc.telefono_dos", "rcc.telefono_tres", "rcc.descripcion","gcd.id as id_direccion","gcd.calle", "gcd.numero_interior", "gcd.numero_exterior", "gcd.cruzamiento_uno", "gcd.cruzamiento_dos", "gcd.codigo_postal", "gcd.colonia", "gcd.localidad", "gcd.municipio", "gcd.estado", "gcd.descripcion as descripcion_direccion","gcd.descripcion as fotografia","gcd.descripcion as extension","gce.estatus")
        ->join("gen_cat_direcciones as gcd","gcd.id","=","rcc.cat_direccion_id")
        ->join("gen_cat_estatus as gce","gce.id","=","rcc.cat_status_id")
        ->where("rcc.id",$id)
        ->where("rcc.activo",1)
        ->get();
        if(count($respuesta)>0){
            $fotografia = DB::table("gen_cat_fotografias")
            ->where("id",$respuesta[0]->cat_fotografia_id)
            ->get();
            if(count($fotografia)>0){
                $respuesta[0]->fotografia = $fotografia[0]->fotografia;
                $respuesta[0]->extension = $fotografia[0]->extension;
            }else{
                $respuesta[0]->fotografia = "";
                $respuesta[0]->extension = "";
            }
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
        //insertar fotografia
        $id_fotografia = $this->getSigId("gen_cat_fotografias");
        DB::insert('insert into gen_cat_fotografias
        (id,nombre, fotografia, extension) values (?, ?, ?, ?)',
        [$id_fotografia,$request["fotografia"]["nombre"],$request["fotografia"]["docB64"],$request["fotografia"]["extension"]]);
        //Insertar dirección
            $id_direccion = $this->getSigId("gen_cat_direcciones");
            $direccion = new Direccion;
            $direccion->id = $id_direccion;
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
            $direccion->fecha_creacion = $this->getHoraFechaActual();
            $direccion->cat_usuario_c_id = 1;
            $direccion->activo = 1;
            $direccion->save();
        //Insertar candidato
        
            $canditado = new Candidato;
            $canditado->id = $this->getSigId("rh_cat_candidatos");
            $canditado->cat_status_id = 6;  //En reclutamiento
            $canditado->cat_clientes_id = $request["cat_clientes_id"];
            $canditado->cat_fotografia_id = $id_fotografia;
            $canditado->cat_direccion_id = $id_direccion;
            $canditado->nombre = strtoupper($request["nombre"]);
            $canditado->apellido_paterno = strtoupper($request["apellido_paterno"]);
            $canditado->apellido_materno = strtoupper($request["apellido_materno"]);
            $canditado->rfc = $request["rfc"];
            $canditado->curp = $request["curp"];
            $canditado->numero_seguro = $request["numero_social"];
            $canditado->fecha_nacimiento = $request["fecha_nacimiento"];
            $canditado->correo = $request["correo"];
            $canditado->telefono =$request["telefono"];
            $canditado->telefono_dos =$request["telefono_dos"];
            $canditado->telefono_tres =$request["telefono_tres"];
            $canditado->descripcion = $request["descripcion"];
            $canditado->fecha_creacion = $this->getHoraFechaActual();
            $canditado->cat_usuario_c_id = 1;
            $canditado->activo = 1;
            $canditado->save();
            return $this->crearRespuesta(1,"El candidato ha sido registrado correctamente",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function eliminarCandidato($id){
        $data = DB::update('update rh_cat_candidatos set activo = 0 where id = ?',[$id]);
        return $this->crearRespuesta(1,"Candidato Eliminado",200);
    }
    public function actualizarCandidato(Request $request){
        //Actualizar candidato
        $canditado = Candidato::find($request["id"]);
        $canditado->nombre = $request["nombre"];
        $canditado->apellido_paterno = $request["apellido_paterno"];
        $canditado->apellido_materno = $request["apellido_materno"];
        $canditado->rfc = $request["rfc"];
        $canditado->curp = $request["curp"];
        $canditado->numero_seguro = $request["numero_social"];
        $canditado->fecha_nacimiento = $request["fecha_nacimiento"];
        $canditado->correo = $request["correo"];
        $canditado->telefono =$request["telefono"];
        $canditado->telefono_dos =$request["telefono_dos"];
        $canditado->telefono_tres =$request["telefono_tres"];
        $canditado->descripcion = $request["descripcion"];
        $canditado->fecha_modificacion = $this->getHoraFechaActual();
        $canditado->cat_usuario_m_id = 1;
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
        $direccion->fecha_modificacion = $this->getHoraFechaActual();
        $direccion->cat_usuario_m_id = 1;
        $direccion->save();
        //Actualizar foto
        DB::update('update gen_cat_fotografias set fotografia = ?, extension = ? where id = ?',[$request["fotografia"]["docB64"],$request["fotografia"]["extension"],$request["fotografia"]["id_fotografia"]]);
        return $this->crearRespuesta(1,"Se ha actualizado con exito",200);
    }
}
