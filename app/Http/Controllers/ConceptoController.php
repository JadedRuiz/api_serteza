<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Concepto;

class ConceptoController extends Controller
{

   public function facServiciosAutocomplete(Request $res)
   {
       $conceptos_sat = DB::table('sat_ClaveProdServ')
       ->select("id_ClaveProdServ","Descripcion")
       ->where(function ($query) use ($res){
            if($res["palabra"] != ""){
                $palabra = "%".$res["palabra"]."%";
                $query->where("Descripcion","like",$palabra)
                ->orWhere("Coincidencias","like",$palabra)
                ->orWhere("ClaveProdServ","like",$palabra);
            }
        })
        ->orderBy("Descripcion","ASC")
        ->limit(100)
        ->get();
        if(count($conceptos_sat)>0){
            return $this->crearRespuesta(1,$conceptos_sat,200);
        }
        return $this->crearRespuesta(2,"No se tiene registros en la BD",200);
   }
   public function facUnidadesAutocomplete(Request $res)
   {
       $unidades_sat = DB::table('sat_UnidadMedida')
       ->where(function ($query) use ($res){
            if($res["palabra"] != ""){
                $palabra = "%".$res["palabra"]."%";
                $query->where("Descripcion","like",$palabra)
                ->orWhere("ClaveUnidad","like",$palabra);
            }
        })
        ->orderBy("Descripcion","ASC")
        ->limit(100)
        ->get();
        if(count($unidades_sat)>0){
            return $this->crearRespuesta(1,$unidades_sat,200);
        }
        return $this->crearRespuesta(2,"No se tiene registros en la BD",200);
   }
   public function facObtenerConceptosEmpresa($id_empresa)
   {
       $conceptos = Concepto::select("id_concepto_empresa","descripcion","descuento","iva","ieps","otros_imp")
       ->where("id_empresa",$id_empresa)
       ->get();
       if(count($conceptos)>0){
           return $this->crearRespuesta(1,$conceptos,200);
       }
       return $this->crearRespuesta(2,"No se han encontrado registros con esta empresa",200);
   }
   public function facObtenerConceptosPorId($id_concepto)
   {
    $conceptos = Concepto::select('id_concepto_empresa', 'satC.id_ClaveProdServ', 'satU.id_UnidadMedida', 'fac_catconceptos.descripcion', 'descuento', 'iva', 'tipo_iva', 'ieps', 'tipo_ieps', 'otros_imp', 'tipo_otros','satC.Descripcion as servicio',"satU.Descripcion as unidad","nombre_otros", 'id_objetoimp', 'satI.clave')
    ->join("sat_ClaveProdServ as satC","satC.id_ClaveProdServ","=","fac_catconceptos.id_ClaveProdServ")
    ->join("sat_UnidadMedida as satU","satU.id_UnidadMedida","=","fac_catconceptos.id_UnidadMedida")
    ->join("sat_objetoimp as satI", "satI.id_objetoimp", "=", "fac_catconceptos.id_objetoimp")
    ->where("id_concepto_empresa",$id_concepto)->first();
    if($conceptos){
        return $this->crearRespuesta(1,$conceptos,200);
    }
    return $this->crearRespuesta(2,"No se ha encontrado el concepto",200);
   }
   public function facAltaConcepto(Request $res)
   {
        $concepto = new Concepto();
        if($res["id_servicio"] == 0){
            return $this->crearRespuesta(2,"El serivicio o producto es un campo obligatorio",200);
        }
        if($res["descripcion"] == ""){
            return $this->crearRespuesta(2,"La descripcion es un campo obligatorio",200);
        }
        if($res["id_unidad"] == 0){
            return $this->crearRespuesta(2,"La unidad de medida es un campo obligatorio",200);
        }
        $validar = DB::table('fac_catconceptos')
        ->where("id_empresa",$res["id_empresa"])
        ->where("id_ClaveProdServ",$res["id_servicio"])
        ->first();
        if($validar){
            return $this->crearRespuesta(2,"Este servicio o producto ya se encuentra registrado en esta empresa",200);
        }
        try{
            $fecha = $this->getHoraFechaActual();
            $concepto->id_empresa = $res["id_empresa"];
            $concepto->id_ClaveProdServ = $res["id_servicio"];
            $concepto->id_UnidadMedida = $res["id_unidad"];
            $concepto->descripcion = strtoupper($res["descripcion"]);
            $concepto->descuento = floatval($res["descuento"]."");
            $concepto->iva = floatval($res["iva"]."");
            $concepto->tipo_iva = $res["tipo_iva"];
            $concepto->ieps = floatval($res["ieps"]."");
            $concepto->tipo_ieps = $res["tipo_ieps"];
            $concepto->otros_imp = floatval($res["otros"]."");
            $concepto->tipo_otros = $res["tipo_otros"];
            $concepto->nombre_otros = $res["nombre_otros"];
            $concepto->fecha_creacion = $fecha;
            $concepto->usuario_creacion = $res["usuario"];
            $concepto->activo = 1;
            $concepto->id_objetoimp = 2;
            $concepto->save();
            return $this->crearRespuesta(1,"Concepto guardado",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),200);
        }   
   }
   public function facModificarConcepto(Request $res)
   {
        if($res["id_servicio"] == 0){
            return $this->crearRespuesta(2,"El serivicio o producto es un campo obligatorio",200);
        }
        if($res["descripcion"] == ""){
            return $this->crearRespuesta(2,"La descripcion es un campo obligatorio",200);
        }
        if($res["id_unidad"] == 0){
            return $this->crearRespuesta(2,"La unidad de medida es un campo obligatorio",200);
        }
        $validar = DB::table('fac_catconceptos')
        ->where("id_concepto_empresa",$res["id_concepto"])
        ->first();
        if($validar){
            $fecha = $this->getHoraFechaActual();
            $concepto = Concepto::find($res["id_concepto"]);
            $concepto->id_ClaveProdServ = $res["id_servicio"];
            $concepto->id_UnidadMedida = $res["id_unidad"];
            $concepto->descripcion = strtoupper($res["descripcion"]);
            $concepto->descuento = floatval($res["descuento"]."");
            $concepto->iva = floatval($res["iva"]."");
            $concepto->tipo_iva = $res["tipo_iva"];
            $concepto->ieps = floatval($res["ieps"]."");
            $concepto->tipo_ieps = $res["tipo_ieps"];
            $concepto->otros_imp = floatval($res["otros"]."");
            $concepto->tipo_otros = $res["tipo_otros"];
            $concepto->nombre_otros = $res["nombre_otros"];
            $concepto->fecha_creacion = $fecha;
            $concepto->usuario_creacion = $res["usuario"];
            $concepto->activo = 1;
            $concepto->save();
            return $this->crearRespuesta(1,"Concepto modificado",200);
        }
        return $this->crearRespuesta(2,"Este concepto no existe o no le pertenece a esta empresa",200);
   }
   public function buscarConceptos(Request $res)
   {
       $palabra = "%".$res["busqueda"]."%";
       $conceptos = DB::table("fac_catconceptos as fcc")
       ->select("fcc.id_concepto_empresa", 'fcc.descripcion', 'fcc.descuento', 'fcc.iva', 'fcc.tipo_iva', 'fcc.ieps', 'fcc.tipo_ieps', 'fcc.otros_imp', 'fcc.tipo_otros', 'fcc.id_objetoimp', 'satI.clave')
       ->join("sat_ClaveProdServ as satC","satC.id_ClaveProdServ","=","fcc.id_ClaveProdServ")
       ->join("sat_objetoimp as satI", "satI.id_objetoimp","=", "fcc.id_objetoimp")
       ->where("fcc.id_empresa",$res["id_empresa"])
       ->where(function ($query) use ($palabra){
           $query->where("fcc.descripcion","like",$palabra)
           ->orWhere("satC.ClaveProdServ","like",$palabra);
       })
       ->get();
       if(count($conceptos)>0){
        return $this->crearRespuesta(1,$conceptos,200);
       }
        return $this->crearRespuesta(2,"Este concepto no existe o no le pertenece a esta empresa",200);
   }

   public function buscarConceptosPorNombre($id_empresa,$concepto)
   {
       $conceptos = DB::table("fac_catconceptos as fcc")
       ->select("fcc.id_concepto_empresa", 'fcc.descripcion', 'fcc.descuento', 'fcc.iva', 'fcc.tipo_iva', 'fcc.ieps', 'fcc.tipo_ieps', 'fcc.otros_imp', 'fcc.tipo_otros', 'fcc.id_objetoimp', 'satI.clave')
       ->join("sat_ClaveProdServ as satC","satC.id_ClaveProdServ","=","fcc.id_ClaveProdServ")
       ->join("sat_objetoimp as satI", "satI.id_objetoimp", "=", "fac_catconceptos.id_objetoimp")
       ->where("fcc.id_empresa",$id_empresa)
       ->where("fcc.descripcion",$concepto)
       ->get();

       if(count($conceptos)>0){
        return $this->crearRespuesta(1,$conceptos,200);
       }
        return $this->crearRespuesta(2,"Este concepto no existe o no le pertenece a esta empresa",200);
   }
}
