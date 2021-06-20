<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Cliente;
use App\Models\Direccion;

class ClienteController extends Controller
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
        $palabra = strtoupper($res["nombre_cliente"]);
        $busqueda = DB::table("gen_cat_cliente as cc")
        ->select("cc.id_cliente","cc.cliente")
        ->where("cc.cliente","like","%".$palabra."%")
        ->where("cc.activo",1)
        ->take(5)
        ->get();
        if(count($busqueda)>0){
            return $this->crearRespuesta(1,$busqueda,200);
        }
        return $this->crearRespuesta(2,"No se han encontrado resultados",200);
    }
    function obtenerClientes(Request $res){
        $take = $res["taken"];
        $pagina = $res["pagina"];
        $status = $res["status"];
        $palabra = $res["palabra"];
        $usuario = $res["usuario"];
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
        $registros = DB::table('gen_cat_cliente')
        ->where("activo",$otro,$status)
        ->where("cliente",$otro_dos,$palabra)
        ->skip($incia)
        ->take($take)
        ->get();
        $contar = DB::table('gen_cat_cliente')
        ->where("activo",$otro,$status)
        ->where("cliente",$otro_dos,$palabra)
        ->get();
        if(count($registros)>0){
            $respuesta = [
                "total" => count($contar),
                "registros" => $registros
            ];
            return $this->crearRespuesta(1,$respuesta,200);
        }else{
            return $this->crearRespuesta(2,"No hay clientes que mostrar",200);
        }
    }
    function obtenerClientesPorId($id){
        $cliente = DB::table("gen_cat_cliente as gcc")
        ->select("gcc.id_cliente","gcc.cliente","gcc.contacto","gcc.descripcion","gcc.activo","gcd.id_direccion","gcd.calle", "gcd.numero_interior", "gcd.numero_exterior", "gcd.cruzamiento_uno", "gcd.cruzamiento_dos", "gcd.codigo_postal", "gcd.colonia", "gcd.localidad", "gcd.municipio", "gcd.estado", "gcd.descripcion as descripcion_direccion","cf.nombre as fotografia","cf.id_fotografia")
        ->join("gen_cat_direccion as gcd","gcd.id_direccion","=","gcc.id_direccion")
        ->join("gen_cat_fotografia as cf","cf.id_fotografia","=","gcc.id_fotografia")
        ->where("gcc.id_cliente",$id)
        ->get();
        if(count($cliente)>0){            
            $cliente[0]->fotografia = Storage::disk('cliente')->url($cliente[0]->fotografia);
            return $this->crearRespuesta(1,$cliente,200);
        }else{
            return $this->crearRespuesta(2,"No se ha encontrado el cliente",301);
        }
    }
    public function obtenerClientesPorIdEmpresa($id_empresa)
    {
        $clientes = DB::table('liga_empresa_cliente as lec')
        ->join("gen_cat_cliente as cc","cc.id_cliente","=","lec.id_cliente")
        ->where("id_empresa",$id_empresa)
        ->get();
        if(count($clientes)>0){
            return $this->crearRespuesta(1,$clientes,200);
        }else{
            return $this->crearRespuesta(2,"No se tienen configurado clientes para esta empresa",200);
        }
    }
    public function obtenerClientePorIdUsuario($id_usuario)
    {
        $clientes_configuradas = DB::table('liga_usuario_cliente as lue')
        ->join("gen_cat_cliente","gen_cat_cliente.id_cliente","lue.id_cliente")
        ->where("id_usuario",$id_usuario)
        ->where("lue.activo",1)
        ->get();
        if(count($clientes_configuradas)>0){
            return $this->crearRespuesta(1,$clientes_configuradas,200);
        }else{
            return $this->crearRespuesta(2,"No se tienen configurado clientes para este usuario",200);
        }
    }
    function altaCliente(Request $request){
        $this->validate($request, [
            'cliente' => 'required|string|max:150|unique:gen_cat_cliente',
            'contacto' => 'required|max:150'
        ]);
        try{
            $id_fotografia = $this->getSigId("gen_cat_fotografia");
            $fecha = $this->getHoraFechaActual();
            $usuario_creacion = $request["usuario_creacion"];
            //Insertar fotografia
            if($request["fotografia"]["docB64"] == ""){
                //Guardar foto default
                DB::insert('insert into gen_cat_fotografia (id_fotografia, nombre, fecha_creacion, usuario_creacion, activo) values (?,?,?,?,?)', [$id_fotografia,"cliente_default.png",$fecha,$usuario_creacion,1]);
            }else{
                $file = base64_decode($request["fotografia"]["docB64"]);
                $nombre_image = "cliente_img_".$id_fotografia.".".$request["fotografia"]["extension"];
                DB::insert('insert into gen_cat_fotografia (id_fotografia, nombre, fecha_creacion, usuario_creacion, activo) values (?,?,?,?,?)', [$id_fotografia,$nombre_image,$fecha,$usuario_creacion,1]);
                Storage::disk('cliente')->put($nombre_image, $file);
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
            //Insertar Cliente
            $id_cliente = $this->getSigId("gen_cat_cliente");
            $cliente = new Cliente;
            $cliente->id_cliente = $id_cliente;
            $cliente->cliente = strtoupper($request["cliente"]);
            $cliente->id_fotografia = $id_fotografia;
            $cliente->contacto = $request["contacto"];
            $cliente->id_direccion = $id_direccion;
            $cliente->descripcion = $request["descripcion"];
            $cliente->fecha_creacion = $fecha;
            $cliente->usuario_creacion = $usuario_creacion;
            $cliente->activo = $request["activo"];
            $cliente->save();
            return $this->crearRespuesta(1,"Se ha guardado exitosamente",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    function actualizarCliente(Request $request){
        try{
            $fecha = $this->getHoraFechaActual();
            $usuario_modificacion = $request["usuario_creacion"];
            $id_fotografia = $request["fotografia"]["id_fotografia"];
            //Actualizar fotografia
            if($request["fotografia"]["docB64"] == ""){
                //Guardar foto default
                DB::update('update gen_cat_fotografia set fecha_modificacion = ?, usuario_modificacion = ? where id_fotografia = ?', [$fecha,$usuario_modificacion,$id_fotografia]);
            }else{
                $file = base64_decode($request["fotografia"]["docB64"]);
                $nombre_image = "cliente_img_".$id_fotografia.".".$request["fotografia"]["extension"];
                if(Storage::disk('cliente')->has($nombre_image)){
                    Storage::disk('cliente')->delete($nombre_image);
                    DB::update('update gen_cat_fotografia set fecha_modificacion = ?, usuario_modificacion = ? where id_fotografia = ?', [$fecha,$usuario_modificacion,$request["fotografia"]["id_fotografia"]]);
                    Storage::disk('cliente')->put($nombre_image, $file);
                }else{
                    DB::update('update gen_cat_fotografia set nombre = ?, fecha_modificacion = ?, usuario_modificacion = ? where id_fotografia = ?', [$nombre_image,$fecha,$usuario_modificacion,$request["fotografia"]["id_fotografia"]]);
                    Storage::disk('cliente')->put($nombre_image, $file);
                }
            }
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
            //Actualizar Cliente
            $cliente = Cliente::find($request["id_cliente"]);
            $cliente->cliente = $request["cliente"];
            $cliente->contacto = $request["contacto"];
            $cliente->descripcion = $request["descripcion"];
            $cliente->fecha_modificacion = $fecha;
            $cliente->usuario_modificacion = $usuario_modificacion;
            $cliente->activo = $request["activo"];
            $cliente->save();
            return $this->crearRespuesta(1,"Cliente Actualizado",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
        

    }
    function eliminarCliente($id){
        try{
            $data = DB::update('update gen_gen_cat_cliente set activo = 0 where id = ?',[$id]);
            return $this->crearRespuesta(1,"Cliente Eliminado",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
        
    }
    public function asignarClienteAUsuario(Request $request){
        try{
            $id_clientes = $request["id_cliente"];
            $id_usuario = $request["id_usuario"];
            foreach($id_clientes as $id_cliente){
                $validar = DB::table('liga_usuario_cliente')
                ->where("id_cliente",$id_cliente)
                ->where("id_usuario",$id_usuario)
                ->get();
                if(count($validar) == 0){
                    $id_liga = $this->getSigId("liga_usuario_cliente");
                    DB::insert('insert into liga_usuario_cliente (id_usuario_cliente, id_usuario, id_cliente, fecha_creacion, usuario_creacion, activo) values (?,?,?,?,?,?)', [$id_liga,$id_usuario,$id_cliente,$this->getHoraFechaActual(),$request["usuario_creacion"],1]);
                }else{
                    if($validar[0]->activo == 0){
                        DB::update('update liga_usuario_cliente set activo = 1 where id_usuario_cliente = ?', [$validar[0]->id_usuario_cliente]);
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
        $id_cliente = $res["id_cliente"];
        $id_usuario = $res["id_usuario"];
        $usuario_modificacion = $res["usuario_creacion"];
        $fecha = $this->getHoraFechaActual();
        try{
            DB::update('update liga_usuario_cliente set activo = 0, fecha_modificacion = ?, usuario_modificacion = ? where id_cliente = ? and id_usuario = ?', [$fecha,$usuario_modificacion,$id_cliente,$id_usuario]);
            return $this->crearRespuesta(1,"Se ha eliminado el cliente al usuario",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
}