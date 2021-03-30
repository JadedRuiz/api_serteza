<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Direccion;
use App\Models\Empresa;

class EmpresaController extends Controller
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

    public function obtenerEmpresa($sistema_id){
        $empresas = DB::table("liga_usuario_empresa as lue")
        ->join("gen_cat_empresas as gce","gce.id","=","lue.cat_empresas_id")
        ->select("gce.id","gce.empresa")
        ->where("lue.usuario_sistemas_id",$sistema_id)
        ->where("lue.activo",1)
        ->where("gce.activo",1)
        ->get();
        if(count($empresas)>0){
            return $this->crearRespuesta(1,$empresas,200);
        }else{
            return $this->crearRespuesta(2,"No se han encontrado empresas configuradas en su usuario",200);
        }
    }
    public function obtenerEmpresaPorId($id){
        $empresa = DB::table("gen_cat_empresas as gce")
        ->select("gce.id","gce.empresa","gce.rfc","gce.razon_social","gce.descripcion","gcd.id as id_direccion","gcd.calle", "gcd.numero_interior", "gcd.numero_exterior", "gcd.cruzamiento_uno", "gcd.cruzamiento_dos", "gcd.codigo_postal", "gcd.colonia", "gcd.localidad", "gcd.municipio", "gcd.estado", "gcd.descripcion as descripcion_direccion","gcd.descripcion as fotografia","gcd.descripcion as extension", "gce.fotografia_id")
        ->join("gen_cat_direcciones as gcd","gcd.id","=","gce.direccion_id")
        ->where("gce.id",$id)
        ->first();
        if($empresa){
            $fotografia = DB::table("gen_cat_fotografias")
            ->where("id",$empresa->fotografia_id)
            ->get();
            if(count($fotografia)>0){
                $empresa->fotografia = $fotografia[0]->fotografia;
                $empresa->extension = $fotografia[0]->extension;
            }else{
                $fotografia = DB::table("gen_cat_fotografias")
                ->where("nombre","empresa_default")
                ->get();
                $empresa->fotografia = $fotografia[0]->fotografia;
                $empresa->extension = $fotografia[0]->extension;
            }
            return $this->crearRespuesta(1,$empresa,200);
        }else{
            return $this->crearRespuesta(2,"No se ha encontrado la empresa",301);
        }
    }
    public function altaEmpresa(Request $request){
        //validate incoming request 
        $this->validate($request, [
            'empresa' => 'required|string|max:300|unique:gen_cat_empresas',
            'razon_social' => 'required|max:300',
            'rfc' => 'required|max:150|unique:gen_cat_empresas',
        ]);
        try{
            //Insertar fotografia
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
            //Insertar Empresa
            $id_empresa = $this->getSigId("gen_cat_empresas");
            $empresa = new Empresa();
            $empresa->id = $id_empresa;
            $empresa->estatus_id = 1;
            $empresa->direccion_id = $id_direccion;
            $empresa->fotografia_id = $id_fotografia;
            $empresa->empresa = $request["empresa"];
            $empresa->razon_social = $request["razon_social"];
            $empresa->rfc = $request["rfc"];
            $empresa->descripcion = $request["descripcion"];
            $empresa->fecha_creacion = $this->getHoraFechaActual();
            $empresa->cat_usuario_c_id = $request["usuario_sistema_id"];
            $empresa->activo = 1;
            $empresa->save();
            //Insertar union usuario-empresa
            $id_liga = $this->getSigId("liga_usuario_empresa");
            DB::insert('insert into liga_usuario_empresa (id, usuario_sistemas_id, cat_empresas_id, fecha_creacion, cat_usuario_c_id, activo) values (?,?,?,?,?,?)', [$id_liga,$request["cat_usuario_id"],$id_empresa,$this->getHoraFechaActual(),$request["usuario_sistema_id"],1]);
            return $this->crearRespuesta(1,"El candidato ha sido registrado correctamente",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function bajaEmpresa($id){
        $data = DB::update('update gen_cat_empresas set activo = 0 where id = ?',[$id]);
        return $this->crearRespuesta(1,"Empresa Eliminada",200);
    }
    public function actualizarEmpresa(Request $request){
        try{
            //Actualizar empresa
            $empresa = Empresa::find($request["id"]);
            $empresa->empresa = $request["empresa"];
            $empresa->razon_social = $request["razon_social"];
            $empresa->rfc = $request["rfc"];
            $empresa->descripcion = $request["descripcion"];
            $empresa->save();
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
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
}
