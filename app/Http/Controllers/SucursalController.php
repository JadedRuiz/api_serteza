<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sucursal;
use App\Models\Direccion;

class SucursalController extends Controller
{
    public function autocomplete(Request $res)
    {
        $id_empresa = $res["id_empresa"];
        $palabra = "%".strtoupper($res["palabra"])."%";
        $sucursales = DB::table('nom_sucursales')
        ->select("id_sucursal","sucursal")
        ->where("id_empresa",$id_empresa)
        ->where("activo",1)
        ->where(function ($query) use ($palabra){
            $query->orWhere("sucursal", "like", $palabra)
            ->orWhere("id_sucursal", "like", $palabra);
        })
        ->get();
        if(count($sucursales)>0){
            return $this->crearRespuesta(1,$sucursales,200);
        }else{
            return $this->crearRespuesta(2,"No se han encontrado resultados",200);
        }
    }
    public function obtenerSucursales($id_empresa)
    {
        $sucursales = DB::table('nom_sucursales as ns')
        ->select("sucursal","gcc.cliente","id_sucursal","zona","representante_legal as repre")
        ->join("gen_cat_cliente as gcc","gcc.id_cliente","=","ns.id_cliente")
        ->where("id_empresa",$id_empresa)
        ->where("ns.activo",1)
        ->get();
        if(count($sucursales)>0){
            return $this->crearRespuesta(1,$sucursales,200);
        }else{
            return $this->crearRespuesta(2,"No se han encontrado sucursales",200);
        }
    }
    public function obtenerSucursalPorIdSucursal($id_sucursal)
    {
        $sucursal = DB::table('nom_sucursales as ns')
        ->select("sucursal","id_cliente","id_sucursal","zona","region","tasaimpuestoestatal","tasaimpuestoespecial","prima_riesgotrabajo","gcd.id_direccion","gcd.calle", "gcd.numero_interior", "gcd.numero_exterior", "gcd.cruzamiento_uno", "gcd.cruzamiento_dos", "gcd.codigo_postal", "gcd.colonia", "gcd.localidad", "gcd.municipio", "gce.estado", "gcd.descripcion as descripcion_direccion","ns.representante_legal","ns.curp","ns.rfc")
        ->leftJoin("gen_cat_direccion as gcd","gcd.id_direccion","=","ns.id_direccion")
        ->leftJoin("gen_cat_estados as gce","gce.id_estado","=","gcd.estado")
        ->where("id_sucursal",$id_sucursal)
        ->where("ns.activo",1)
        ->get();
        if(count($sucursal)>0){
            return $this->crearRespuesta(1,$sucursal,200);
        }else{
            return $this->crearRespuesta(2,"No se han encontrado la sucursal",200);
        }
    }
    public function crearSucursal(Request $request)
    {
        try{
            $id_direccion = 0;
            if(isset($request["direccion"])){
                $direccion = new Direccion();
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
                $direccion->fecha_modificacion = $this->getHoraFechaActual();
                $direccion->usuario_modificacion = $request["usuario"];
                $direccion->save();
                $id_direccion = $direccion->id_direccion;
            }
            $id_empresa = $request["id_empresa"];
            $id_cliente = $request["id_cliente"];
            $id_sucursal = $this->getSigId("nom_sucursales");
            if(isset($request["representante"]["nombre"])){
                $sucursal->representante_legal = strtoupper($request["representante"]["nombre"]);
            }
            if(isset($request["representante"]["rfc"])){
                $sucursal->rfc = strtoupper($request["representante"]["rfc"]);
            }
            if(isset($request["representante"]["curp"])){
                $sucursal->curp = strtoupper($request["representante"]["curp"]);
            }
            $sucursal = strtoupper($request["sucursal"]);
            $zona = strtoupper($request["zona"]);
            $tasa_estatal = $request["tasa_estatal"];
            $tasa_especial = $request["tasa_especial"];
            $prima_riesgo = $request["prima_riesgo"];
            $id_direccion = $id_direccion;
            $region = strtoupper($request["region"]);
            $usuario_creacion = $request["usuario"];
            $fecha = $this->getHoraFechaActual();
            DB::insert('insert into nom_sucursales (id_sucursal, id_empresa, id_cliente, sucursal, zona, region, tasaimpuestoestatal, tasaimpuestoespecial, prima_riesgotrabajo, id_direccion, usuario_creacion, fecha_creacion, activo) values (?,?,?,?,?,?,?,?,?,?,?,?,?)', [$id_sucursal,$id_empresa,$id_cliente,$sucursal,$zona,$region,$tasa_estatal,$tasa_especial,$prima_riesgo,$id_direccion,$usuario_creacion,$fecha, 1]);
            return $this->crearRespuesta(1,"Sucursal creada",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }

    public function modificarSucursal(Request $request)
    {
        try{
            $sucursal = Sucursal::find($request["id_sucursal"]);
            $sucursal->sucursal = strtoupper($request["sucursal"]);
            $sucursal->region = strtoupper($request["region"]);
            $sucursal->zona = $request["zona"];
            $sucursal->tasaimpuestoestatal = $request["tasa_estatal"];
            $sucursal->tasaimpuestoespecial = $request["tasa_especial"];
            $sucursal->prima_riesgotrabajo = $request["prima_riesgo"];
            if(isset($request["representante"]["nombre"])){
                $sucursal->representante_legal = strtoupper($request["representante"]["nombre"]);
            }
            if(isset($request["representante"]["rfc"])){
                $sucursal->rfc = strtoupper($request["representante"]["rfc"]);
            }
            if(isset($request["representante"]["curp"])){
                $sucursal->curp = strtoupper($request["representante"]["curp"]);
            }
            $sucursal->usuario_creacion = $request["usuario"];
            $sucursal->fecha_creacion = $this->getHoraFechaActual();
            $sucursal->activo = 1;
            $sucursal->save();
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
            $direccion->fecha_modificacion = $this->getHoraFechaActual();
            $direccion->usuario_modificacion = $request["usuario"];
            $direccion->save();
            return $this->crearRespuesta(1,"Sucursal modificada",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
}
