<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
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
        $registros = DB::table('cat_cliente')
        ->where("activo",$otro,$status)
        ->where("cliente",$otro_dos,$palabra)
        ->skip($incia)
        ->take($take)
        ->get();
        $contar = DB::table('cat_cliente')
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
        $cliente = DB::table("cat_cliente as gcc")
        ->select("gcc.id_cliente","gcc.cliente","gcc.contacto","gcc.descripcion","gcc.activo","gcd.id_direccion","gcd.calle", "gcd.numero_interior", "gcd.numero_exterior", "gcd.cruzamiento_uno", "gcd.cruzamiento_dos", "gcd.codigo_postal", "gcd.colonia", "gcd.localidad", "gcd.municipio", "gcd.estado", "gcd.descripcion as descripcion_direccion")
        ->join("cat_direccion as gcd","gcd.id_direccion","=","gcc.id_direccion")
        ->where("gcc.id_cliente",$id)
        ->get();
        if(count($cliente)>0){
            return $this->crearRespuesta(1,$cliente,200);
        }else{
            return $this->crearRespuesta(2,"No se ha encontrado el cliente",301);
        }
    }
    public function obtenerClientesPorIdEmpresa($id_empresa)
    {
        $clientes = DB::table('liga_empresa_cliente as lec')
        ->join("cat_cliente as cc","cc.id_cliente","=","lec.id_cliente")
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
        ->join("cat_cliente","cat_cliente.id_cliente","lue.id_cliente")
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
            'cliente' => 'required|string|max:150',
            'contacto' => 'required|max:150'
        ]);
        try{
            //Insertar dirección
            $id_direccion = $this->getSigId("cat_direccion");
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
            $direccion->fecha_creacion = $this->getHoraFechaActual();
            $direccion->usuario_creacion = $request["usuario_creacion"];;
            $direccion->activo = 1;
            $direccion->save();
            //Insertar Cliente
            $id_cliente = $this->getSigId("cat_cliente");
            $cliente = new Cliente;
            $cliente->id_cliente = $id_cliente;
            $cliente->cliente = $request["cliente"];
            $cliente->contacto = $request["contacto"];
            $cliente->id_direccion = $id_direccion;
            $cliente->descripcion = $request["descripcion"];
            $cliente->fecha_creacion = $this->getHoraFechaActual();
            $cliente->usuario_creacion = $request["usuario_creacion"];
            $cliente->activo = $request["activo"];
            $cliente->save();
            //Asignar cliente al usuario
            // $id_liga = $this->getSigId("liga_usuario_cliente");
            // DB::insert('insert into liga_usuario_cliente (id, usuario_sistemas_id, cliente_id, fecha_creacion, cat_usuario_c_id, activo) values (?,?,?,?,?,?)', [$id_liga,$request["cat_usuario_sistema"],$id_cliente,$this->getHoraFechaActual(),$request["cat_usuario_id"],1]);
            return $this->crearRespuesta(1,"Se ha guardado exitosamente",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    function actualizarCliente(Request $request){
        try{
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
            $direccion->usuario_modificacion = $request["usuario"];
            $direccion->save();
            //Actualizar Cliente
            $cliente = Cliente::find($request["id_cliente"]);
            $cliente->cliente = $request["cliente"];
            $cliente->contacto = $request["contacto"];
            $cliente->descripcion = $request["descripcion"];
            $cliente->fecha_modificacion = $this->getHoraFechaActual();
            $cliente->usuario_modificacion = $request["usuario"];
            $cliente->activo = $request["activo"];
            $cliente->save();
            return $this->crearRespuesta(1,"Cliente Actualizado",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
        

    }
    function eliminarCliente($id){
        try{
            $data = DB::update('update gen_cat_cliente set activo = 0 where id = ?',[$id]);
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