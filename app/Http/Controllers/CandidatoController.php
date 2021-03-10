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
        $usuario_totales = count(Candidato::all());
        $usuarios_contratatos = count(Candidato::where("cat_status_id",1)->get());
        $usuarios_por_contratar = count(Candidato::where("cat_status_id",2)->get());
        $numero_solicitudes = 0; //Aun no se como sacarlo
        $respuesta = [
            "num_solicitudes" => $numero_solicitudes,
            "por_contratar" => $usuarios_por_contratar,
            "contratados" => $usuarios_contratatos,
            "total" => $usuario_totales
        ];
        return $this->crearRespuesta(1,$respuesta,200);
    }

    public function altaCandidato(Request $request){
        //validate incoming request 
        $this->validate($request, [
            'nombre' => 'required|string|max:150',
            'apellido_paterno' => 'required|max:150',
            'apellido_materno' => 'required|max:150',
        ]);
        try{
        //Insertar dirección
            $direccion = new Direccion;
            $direccion->id = $this->getSigId("gen_cat_direcciones");
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
            $direccion->save();
            $id_direccion = $this->getSigId("gen_cat_direcciones");
        //Insertar candidato
        
            $canditado = new Candidato;
            $canditado->id = $this->getSigId("rh_cat_candidatos");
            $canditado->cat_status_id = 6;  //En reclutamiento
            $canditado->cat_clientes_id = $request["id_cliente"];
            $canditado->cat_fotografia_id = null;
            $canditado->cat_direccion_id = $id_direccion;
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
            $canditado->fecha_creacion = $this->getHoraFechaActual();
            $canditado->cat_usuario_c_id = 1;
            $canditado->activo = 1;
            $canditado->save();
            return $this->crearRespuesta(1,"Everything ok",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function pruebaOtro(){
        return $this->crearRespuesta(1,"Everything ok",200);
    }
}
