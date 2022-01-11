<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Direccion;
use App\Models\Empresa;
use Illuminate\Support\Facades\Storage;

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
    public function autoComplete(Request $res){
        $palabra = strtoupper($res["nombre_empresa"]);
        $busqueda = DB::table("gen_cat_empresa as ce")
        ->select("ce.id_empresa","ce.empresa")
        ->where("ce.empresa","like","%".$palabra."%")
        ->where("ce.activo",1)
        ->take(5)
        ->get();
        if(count($busqueda)>0){
            return $this->crearRespuesta(1,$busqueda,200);
        }
        return $this->crearRespuesta(2,"No se han encontrado resultados",200);
    }
    public function obtenerEmpresas(Request $res){
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
        $registros = DB::table('gen_cat_empresa')
        ->select("empresa","id_empresa","id_empresa as id_entidad","empresa as entidad")
        ->where("id_status",$otro,$status)
        ->where("empresa",$otro_dos,$palabra)
        ->skip($incia)
        ->take($take)
        ->get();
        $contar = DB::table('gen_cat_empresa')
        ->where("id_status",$otro,$status)
        ->where("empresa",$otro_dos,$palabra)
        ->get();
        if(count($registros)>0){
            $respuesta = [
                "total" => count($contar),
                "registros" => $registros
            ];
            return $this->crearRespuesta(1,$respuesta,200);
        }else{
            return $this->crearRespuesta(2,"No hay empresas que mostrar",200);
        }
    }
    public function obtenerEmpresaPorId($id){
        $empresa = DB::table("gen_cat_empresa as gce")
        ->select("gce.id_empresa","gce.empresa","gce.rfc","gce.razon_social","gce.descripcion","gcd.id_direccion","gcd.calle", "gcd.numero_interior", "gcd.numero_exterior", "gcd.cruzamiento_uno", "gcd.cruzamiento_dos", "gcd.codigo_postal", "gcd.colonia", "gcd.localidad", "gcd.municipio","gced.id_estado", "gced.estado", "gcd.descripcion as descripcion_direccion","gcd.descripcion as fotografia","gcd.descripcion as extension", "gce.id_fotografia","gce.activo","gce.representante_legal","gce.rfc_repre","gce.curp")
        ->join("gen_cat_direccion as gcd","gcd.id_direccion","=","gce.id_direccion")
        ->leftJoin("gen_cat_estados as gced","gced.id_estado","=","gcd.estado")
        ->where("gce.id_empresa",$id)
        ->get();
        if(count($empresa)>0){
            $fotografia = DB::table("gen_cat_fotografia")
            ->where("id_fotografia",$empresa[0]->id_fotografia)
            ->get();
            $empresa[0]->fotografia = Storage::disk('empresa')->url($fotografia[0]->nombre);
            
            return $this->crearRespuesta(1,$empresa,200);
        }else{
            return $this->crearRespuesta(2,"No se ha encontrado la empresa",301);
        }
    }
    public function obtenerEmpresaPorIdUsuario($id_usuario)
    {
        $empresas_configuradas = DB::table('liga_usuario_empresa as lue')
        ->join("gen_cat_empresa","gen_cat_empresa.id_empresa","lue.id_empresa")
        ->where("id_usuario",$id_usuario)
        ->where("lue.activo",1)
        ->get();
        if(count($empresas_configuradas)>0){
            return $this->crearRespuesta(1,$empresas_configuradas,200);
        }else{
            return $this->crearRespuesta(2,"No se tienen configurado empresas para este usuario",200);
        }
    }
    public function altaEmpresa(Request $request){
        //validate incoming request 
        $this->validate($request, [
            'empresa' => 'required|string|max:300|unique:gen_cat_empresa',
            'razon_social' => 'required|max:300',
            'rfc' => 'required|max:150|unique:gen_cat_empresa',
        ]);
        try{
            //Insertar fotografia
            $id_fotografia = $this->getSigId("gen_cat_fotografia");
            $fecha = $this->getHoraFechaActual();
            $usuario_creacion = $request["usuario_creacion"];
            if($request["fotografia"]["docB64"] == ""){
                //Guardar foto default
                DB::insert('insert into gen_cat_fotografia (id_fotografia, nombre, fecha_creacion, usuario_creacion, activo) values (?,?,?,?,?)', [$id_fotografia,"empresa_default.png",$fecha,$usuario_creacion,1]);
            }else{
                $file = base64_decode($request["fotografia"]["docB64"]);
                $nombre_image = "empresa_img_".$id_fotografia.".".$request["fotografia"]["extension"];
                DB::insert('insert into gen_cat_fotografia (id_fotografia, nombre, fecha_creacion, usuario_creacion, activo) values (?,?,?,?,?)', [$id_fotografia,$nombre_image,$fecha,$usuario_creacion,1]);
                Storage::disk('empresa')->put($nombre_image, $file); 
            }
            //Insertar dirección
            $id_direccion = $this->getSigId("gen_cat_direccion");
            $direccion = new Direccion;
            $direccion->id_direccion = $id_direccion;
            $direccion->calle = strtoupper($request["direccion"]["calle"]);
            $direccion->numero_interior = strtoupper($request["direccion"]["numero_interior"]);
            $direccion->numero_exterior = strtoupper($request["direccion"]["numero_exterior"]);
            $direccion->cruzamiento_uno = strtoupper($request["direccion"]["cruzamiento_uno"]);
            $direccion->cruzamiento_dos = strtoupper($request["direccion"]["cruzamiento_dos"]);
            $direccion->codigo_postal = $request["direccion"]["codigo_postal"];
            $direccion->colonia = strtoupper($request["direccion"]["colonia"]);
            $direccion->localidad = strtoupper($request["direccion"]["localidad"]);
            $direccion->municipio = strtoupper($request["direccion"]["municipio"]);
            $direccion->estado = $request["direccion"]["estado"];
            $direccion->descripcion = strtoupper($request["direccion"]["descripcion"]);
            $direccion->fecha_creacion = $fecha;
            $direccion->usuario_creacion = $usuario_creacion;
            $direccion->activo = $request["activo"];
            $direccion->save();
            //Insertar Empresa
            $id_empresa = $this->getSigId("gen_cat_empresa");
            $empresa = new Empresa();
            $empresa->id_empresa = $id_empresa;
            $empresa->id_status = 1;
            $empresa->id_direccion = $id_direccion;
            $empresa->id_fotografia = $id_fotografia;
            $empresa->empresa = strtoupper($request["empresa"]);
            $empresa->razon_social = strtoupper($request["razon_social"]);
            $empresa->rfc = strtoupper($request["rfc"]);
            $empresa->descripcion = strtoupper($request["descripcion"]);
            if(isset($request["representante"]["nombre"])){
                $empresa->representante_legal = strtoupper($request["representante"]["nombre"]);
            }
            if(isset($request["representante"]["rfc"])){
                $empresa->rfc_repre = strtoupper($request["representante"]["rfc"]);
            }
            if(isset($request["representante"]["curp"])){
                $empresa->curp = strtoupper($request["representante"]["curp"]);
            }
            $empresa->fecha_creacion = $fecha;
            $empresa->usuario_creacion = $usuario_creacion;
            $empresa->activo = $request["activo"];
            $empresa->save();
            return $this->crearRespuesta(1,"La empresa ha sido registrada correctamente",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function bajaEmpresa($id){
        $data = DB::update('update gen_gen_cat_empresas set activo = 0 where id = ?',[$id]);
        return $this->crearRespuesta(1,"Empresa Eliminada",200);
    }
    public function actualizarEmpresa(Request $request){
        try{
            //Actualizar foto
            $fecha = $this->getHoraFechaActual();
            $usuario_modificacion = $request["usuario_creacion"];
            $id_fotografia = $request["fotografia"]["id_fotografia"];
            //Actualizar fotografia
            if($request["fotografia"]["docB64"] == ""){
                //Guardar foto default
                DB::update('update gen_cat_fotografia set fecha_modificacion = ?, usuario_modificacion = ? where id_fotografia = ?', [$fecha,$usuario_modificacion,$id_fotografia]);
            }else{
                $file = base64_decode($request["fotografia"]["docB64"]);
                $nombre_image = "empresa_img_".$id_fotografia.".".$request["fotografia"]["extension"];
                if(Storage::disk('empresa')->has($nombre_image)){
                    Storage::disk('empresa')->delete($nombre_image);
                    DB::update('update gen_cat_fotografia set fecha_modificacion = ?, usuario_modificacion = ? where id_fotografia = ?', [$fecha,$usuario_modificacion,$request["fotografia"]["id_fotografia"]]);
                    Storage::disk('empresa')->put($nombre_image, $file);
                }else{
                    DB::update('update gen_cat_fotografia set nombre = ?, fecha_modificacion = ?, usuario_modificacion = ? where id_fotografia = ?', [$nombre_image,$fecha,$usuario_modificacion,$request["fotografia"]["id_fotografia"]]);
                    Storage::disk('empresa')->put($nombre_image, $file);
                }
            }
            //Actualizar empresa
            $empresa = Empresa::find($request["id_empresa"]);
            $empresa->empresa = strtoupper($request["empresa"]);
            $empresa->razon_social = strtoupper($request["razon_social"]);
            $empresa->rfc = strtoupper($request["rfc"]);
            $empresa->descripcion = strtoupper($request["descripcion"]);
            if(isset($request["representante"]["nombre"])){
                $empresa->representante_legal = strtoupper($request["representante"]["nombre"]);
            }
            if(isset($request["representante"]["rfc"])){
                $empresa->rfc_repre = strtoupper($request["representante"]["rfc"]);
            }
            if(isset($request["representante"]["curp"])){
                $empresa->curp = strtoupper($request["representante"]["curp"]);
            }
            $empresa->fecha_modificacion = $fecha;
            $empresa->usuario_modificacion = $usuario_modificacion;
            $empresa->save();
            //Actualizar direccion
            $direccion = Direccion::find($request["direccion"]["id_direccion"]);
            $direccion->calle = strtoupper($request["direccion"]["calle"]);
            $direccion->numero_interior = $request["direccion"]["numero_interior"];
            $direccion->numero_exterior = $request["direccion"]["numero_exterior"];
            $direccion->cruzamiento_uno = $request["direccion"]["cruzamiento_uno"];
            $direccion->cruzamiento_dos = $request["direccion"]["cruzamiento_dos"];
            $direccion->codigo_postal = $request["direccion"]["codigo_postal"];
            $direccion->colonia = strtoupper($request["direccion"]["colonia"]);
            $direccion->localidad = strtoupper($request["direccion"]["localidad"]);
            $direccion->municipio = strtoupper($request["direccion"]["municipio"]);
            $direccion->estado = $request["direccion"]["estado"];
            $direccion->descripcion = strtoupper($request["direccion"]["descripcion"]);
            $direccion->fecha_modificacion = $fecha;
            $direccion->usuario_modificacion = $usuario_modificacion;
            $direccion->save();
            return $this->crearRespuesta(1,"Se ha actualizado con exito",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function asignarEmpresaAUsuario(Request $request){
        try{
            $id_empresas = $request["id_empresa"];
            $id_usuario = $request["id_usuario"];
            foreach($id_empresas as $id_empresa){
                $validar = DB::table('liga_usuario_empresa')
                ->where("id_empresa",$id_empresa)
                ->where("id_usuario",$id_usuario)
                ->get();
                if(count($validar) == 0){
                    $id_liga = $this->getSigId("liga_usuario_empresa");
                    DB::insert('insert into liga_usuario_empresa (id_usuario_empresa, id_usuario, id_empresa, fecha_creacion, usuario_creacion, activo) values (?,?,?,?,?,?)', [$id_liga,$id_usuario,$id_empresa,$this->getHoraFechaActual(),$request["usuario_creacion"],1]);
                }else{
                    if($validar[0]->activo == 0){
                        DB::update('update liga_usuario_empresa set activo = 1 where id_usuario_empresa = ?', [$validar[0]->id_usuario_empresa]);
                    }
                }
            }
            return $this->crearRespuesta(1,"Se han agreado las empresas al usuario",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function elimiminarLiga(Request $res)
    {
        $id_empresa = $res["id_empresa"];
        $id_usuario = $res["id_usuario"];
        $usuario_modificacion = $res["usuario_creacion"];
        $fecha = $this->getHoraFechaActual();
        try{
            DB::update('update liga_usuario_empresa set activo = 0, fecha_modificacion = ?, usuario_modificacion = ? where id_empresa = ? and id_usuario = ?', [$fecha,$usuario_modificacion,$id_empresa,$id_usuario]);
            return $this->crearRespuesta(1,"Se ha eliminado la empresa al usuario",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function ligarClienteAEmpresa(Request $request)
    {
        try{
            $id_empresa = $request["id_empresa"];
            $id_clientes = $request["id_cliente"];
            $usuario_creacion = $request["usuario_creacion"];
            foreach($id_clientes as $id_cliente){
                $validar = DB::table('liga_empresa_cliente')
                ->where("id_empresa",$id_empresa)
                ->where("id_cliente",$id_cliente)
                ->get();
                if(count($validar) == 0){
                    $id_liga = $this->getSigId("liga_empresa_cliente");
                    DB::insert('insert into liga_empresa_cliente (id_empresa_cliente, id_cliente, id_empresa, fecha_creacion, usuario_creacion, activo) values (?,?,?,?,?,?)', [$id_liga,$id_cliente,$id_empresa,$this->getHoraFechaActual(),$usuario_creacion,1]);
                }else{
                    if($validar[0]->activo == 0){
                        DB::update('update liga_empresa_cliente set activo = 1 where id_empresa_cliente = ?', [$validar[0]->id_empresa_cliente]);
                    }
                }
            }
            return $this->crearRespuesta(1,"Se han agreado el cliente a la empresa",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }

    public function obtenerEmpresasPorIdCliente($id_cliente)     
    {
        $empresas = DB::table('liga_empresa_cliente as lec')
        ->select("lec.id_empresa",DB::raw("CONCAT('(',lec.id_empresa,')',' ',ce.empresa) as empresa"),"ce.empresa as nombre","ce.razon_social","representante_legal as repre")
        ->join("gen_cat_empresa as ce","ce.id_empresa","=","lec.id_empresa")
        ->where("lec.id_cliente",$id_cliente)
        ->get();
        if(count($empresas)>0){
            return $this->crearRespuesta(1,$empresas,200);
        }else{
            return $this->crearRespuesta(2,"No se tiene congifurados empresas a este cliente",200);
        }
    }
}
