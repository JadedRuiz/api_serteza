<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ConConcepto;

class conConceptoController extends Controller
{

   public function ConceptosAutocomplete(Request $res)
   {
    
       $conceptos = DB::table('con_catconceptos')
       ->select("id_concepto","concepto")
       ->where(function ($query) use ($res){
            if($res["palabra"] != ""){
                $palabra = "%".$res["palabra"]."%";
                $query->where("id_empresa",$res["id_empresa"])
                ->Where("concepto","like",$palabra);
                
            }
        })
        ->orderBy("concepto","ASC")
        ->limit(100)
        ->get();
        
        // if(count($conceptos)>0){
            return $this->crearRespuesta(1,$conceptos,200);
        // }
        // return $this->crearRespuesta(2,"No se tiene registros en la BD",200);
   }
   
   public function ConceptosEmpresa($id_empresa)
   {
       $conceptos = conConcepto::select("id_concepto","concepto","cuentacontable","confactura","cancelaiva","tipomovimiento","nomina")
       ->where("id_empresa",$id_empresa)
       ->orderBy("concepto","ASC")
       ->get();
       if(count($conceptos)>0){
           return $this->crearRespuesta(1,$conceptos,200);
       }
       return $this->crearRespuesta(2,"No se han encontrado registros con esta empresa",200);
   }
   public function ObtenerConceptoPorId($id_concepto)
   {
    $conceptos = conConcepto::select("id_concepto","concepto","cuentacontable","confactura","cancelaiva","tipomovimiento","nomina")
    ->where("id_concepto",$id_concepto)->first();
    if($conceptos){
        return $this->crearRespuesta(1,$conceptos,200);
    }
    return $this->crearRespuesta(2,"No se ha encontrado el concepto",200);
   }
   public function AltaConcepto(Request $res)
   {
        $concepto = new conConcepto();
        if($res["id_empresa"] == 0){
            return $this->crearRespuesta(2,"Debe capturar el ID Empresa, es un campo obligatorio",200);
        }
        if($res["concepto"] == ""){
            return $this->crearRespuesta(2,"La descripcion del concepto es obligatorio",200);
        }
        try{
            $fecha = $this->getHoraFechaActual();
            $concepto->id_empresa = $res["id_empresa"];
            $concepto->id_concepto = $res["id_concepto"];
            $concepto->concepto = $res["concepto"];
            $concepto->cuentacontable = $res["cuentacontable"];
            $concepto->confactura = $res["confactura"];
            $concepto->cancelaiva = $res["cancelaiva"];
            $concepto->tipomovimiento = $res["tipomovimiento"];
            $concepto->nomina = $res["nomina"];
            $concepto->fecha_creacion = $fecha;
            $concepto->usuario_creacion = $res["usuario"];
            $concepto->save();
            return $this->crearRespuesta(1,"Concepto guardado",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),200);
        }   
   }
   public function ModificarConcepto(Request $res)
   {
        if($res["id_empresa"] == 0){
            return $this->crearRespuesta(2,"Debe capturar el ID Empresa, es un campo obligatorio",200);
        }
        if($res["concepto"] == ""){
            return $this->crearRespuesta(2,"La descripcion del concepto es obligatorio",200);
        }
            $fecha = $this->getHoraFechaActual();
            $concepto = conConcepto::find($res["id_concepto"]);
            $concepto->id_empresa = $res["id_empresa"];
            $concepto->id_concepto = $res["id_concepto"];
            $concepto->concepto = $res["concepto"];
            $concepto->cuentacontable = $res["cuentacontable"];
            $concepto->confactura = $res["confactura"];
            $concepto->cancelaiva = $res["cancelaiva"];
            $concepto->nomina = $res["nomina"];
            $concepto->tipomovimiento = $res["tipomovimiento"];
            $concepto->fecha_creacion = $fecha;
            $concepto->usuario_creacion = $res["usuario"];
            $concepto->save();
            return $this->crearRespuesta(1,"Concepto modificado",200);
        
   }
   
}
