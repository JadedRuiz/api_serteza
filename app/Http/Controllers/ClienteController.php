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

    function obtenerClientes($usuario_sistema_id){
        $cliente = DB::table("liga_usuario_cliente as luc")
        ->join("gen_cat_cliente as gcc","gcc.id","=","luc.cliente_id")
        ->select("gcc.id","gcc.cliente")
        ->where("luc.usuario_sistemas_id",$usuario_sistema_id)
        ->where("luc.activo",1)
        ->where("gcc.activo",1)
        ->orderBy("gcc.cliente","ASC")
        ->get();
        if(count($cliente)>0){
            return $this->crearRespuesta(1,$cliente,200);
        }else{
            return $this->crearRespuesta(2,"No se han encontrado clientes configuradas en su usuario",200);
        }
    }
    function obtenerClientesPorId($id){
        $cliente = DB::table("gen_cat_cliente as gcc")
        ->select("gcc.id as id_cliente","gcc.cliente","gcc.contacto","gcc.descripcion","gcd.id as id_direccion","gcd.calle", "gcd.numero_interior", "gcd.numero_exterior", "gcd.cruzamiento_uno", "gcd.cruzamiento_dos", "gcd.codigo_postal", "gcd.colonia", "gcd.localidad", "gcd.municipio", "gcd.estado", "gcd.descripcion as descripcion_direccion")
        ->join("gen_cat_direcciones as gcd","gcd.id","=","gcc.direccion_id")
        ->where("gcc.id",$id)
        ->get();
        if(count($cliente)>0){
            return $this->crearRespuesta(1,$cliente,200);
        }else{
            return $this->crearRespuesta(2,"No se ha encontrado el cliente",301);
        }
    }
    function altaCliente(Request $request){
        $this->validate($request, [
            'cliente' => 'required|string|max:150',
            'contacto' => 'required|max:150'
        ]);
        try{
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
            //Insertar Cliente
            $id_cliente = $this->getSigId("gen_cat_cliente");
            $cliente = new Cliente;
            $cliente->id = $id_cliente;
            $cliente->cliente = $request["cliente"];
            $cliente->contacto = $request["contacto"];
            $cliente->direccion_id = $id_direccion;
            $cliente->descripcion = $request["descripcion"];
            $cliente->fecha_creacion = $this->getHoraFechaActual();
            $cliente->cat_usuario_c_id = 1;
            $cliente->activo = 1;
            $cliente->save();
            //Asignar cliente al usuario
            $id_liga = $this->getSigId("liga_usuario_cliente");
            DB::insert('insert into liga_usuario_cliente (id, usuario_sistemas_id, cliente_id, fecha_creacion, cat_usuario_c_id, activo) values (?,?,?,?,?,?)', [$id_liga,$request["cat_usuario_sistema"],$id_cliente,$this->getHoraFechaActual(),$request["cat_usuario_id"],1]);
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
            $direccion->cat_usuario_m_id = 1;
            $direccion->save();
            //Actualizar Cliente
            $cliente = Cliente::find($request["id"]);
            $cliente->cliente = $request["cliente"];
            $cliente->contacto = $request["contacto"];
            $cliente->descripcion = $request["descripcion"];
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
}