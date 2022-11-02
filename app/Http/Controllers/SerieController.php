<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Serie;
use App\Models\Direccion;
use App\Models\Factura;

class SerieController extends Controller
{
    public function facObtenerFolioSig($id_serie)
    {
        $folio = Factura::select("folio")
        ->where("id_serie",$id_serie)
        ->orderBy(DB::raw("cast(folio as integer)"),"desc")
        ->first();
        if($folio){
            return $this->crearRespuesta(1,intval($folio->folio)+1,200);
        }else{
            return $this->crearRespuesta(1,1,200);
        }
    }
    public function obtenerSeries($id_empresa)
    {
        $series = Serie::select("id_serie","serie",DB::raw("CONCAT('CALLE ',gcd.calle,' # ',gcd.numero_exterior,' x ', gcd.cruzamiento_uno, ' COL. ',gcd.colonia) as direccion"))
        ->join("gen_cat_direccion as gcd","gcd.id_direccion","=","fac_catseries.id_direccion")
        ->where("id_empresa",$id_empresa)
        ->get();
        if(count($series)>0){
            return $this->crearRespuesta(1,$series,200);
        }
        return $this->crearRespuesta(2,"No se han encontrado series",200);
    }
    public function obtenerSeriePorId($id_serie)
    {
        $serie = Serie::select("id_serie","serie","gcd.id_direccion","gcd.calle", "gcd.numero_interior", "gcd.numero_exterior", "gcd.cruzamiento_uno", "gcd.cruzamiento_dos", "gcd.codigo_postal", "gcd.colonia", "gcd.localidad", "gcd.municipio", "gcd.estado", "gcd.descripcion","gce.estado","gcd.estado as id_estado")
        ->join("gen_cat_direccion as gcd","gcd.id_direccion","=","fac_catseries.id_direccion")
        ->join("gen_cat_estados as gce","gce.id_estado","=","gcd.estado")
        ->where("id_serie",$id_serie)
        ->first();
        if($serie){
            return $this->crearRespuesta(1,$serie,200);
        }
        return $this->crearRespuesta(2,"No se ha encontrado la serie",200);
    }
    public function altaSerie(Request $res)
    {
        try{
            $fecha = $this->getHoraFechaActual();
            $usuario_creacion = $res["usuario_creacion"];
            $direccion = new Direccion();
            $direccion->calle = strtoupper($res["direccion"]["calle"]);
            $direccion->numero_interior = $res["direccion"]["numero_interior"];
            $direccion->numero_exterior = $res["direccion"]["numero_exterior"];
            $direccion->cruzamiento_uno = $res["direccion"]["cruzamiento_uno"];
            $direccion->cruzamiento_dos = $res["direccion"]["cruzamiento_dos"];
            $direccion->codigo_postal = $res["direccion"]["codigo_postal"];
            $direccion->colonia = strtoupper($res["direccion"]["colonia"]);
            $direccion->localidad = strtoupper($res["direccion"]["localidad"]);
            $direccion->municipio = strtoupper($res["direccion"]["municipio"]);
            $direccion->estado = $res["direccion"]["estado"];
            $direccion->descripcion = strtoupper($res["direccion"]["descripcion"]);
            $direccion->fecha_creacion = $fecha;
            $direccion->usuario_creacion = $usuario_creacion;
            $direccion->activo = 1;
            $direccion->save();
            $id_direccion = $direccion->id_direccion;
            $serie = new Serie();
            $serie->id_empresa = $res["id_empresa"];
            $serie->id_direccion = $id_direccion;
            $serie->serie = strtoupper($res["serie"]);
            $serie->fecha_creacion = $fecha;
            $serie->usuario_creacion = $usuario_creacion;
            $serie->activo = 1;
            $serie->save();
            return $this->crearRespuesta(1,"Serie dado de alta",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function modificarSerie(Request $res)
    {
        try{
            $fecha = $this->getHoraFechaActual();
            $usuario_creacion = $res["usuario_creacion"];
            $direccion = Direccion::find($res["direccion"]["id_direccion"]);
            $direccion->calle = strtoupper($res["direccion"]["calle"]);
            $direccion->numero_interior = $res["direccion"]["numero_interior"];
            $direccion->numero_exterior = $res["direccion"]["numero_exterior"];
            $direccion->cruzamiento_uno = $res["direccion"]["cruzamiento_uno"];
            $direccion->cruzamiento_dos = $res["direccion"]["cruzamiento_dos"];
            $direccion->codigo_postal = $res["direccion"]["codigo_postal"];
            $direccion->colonia = strtoupper($res["direccion"]["colonia"]);
            $direccion->localidad = strtoupper($res["direccion"]["localidad"]);
            $direccion->municipio = strtoupper($res["direccion"]["municipio"]);
            $direccion->estado = $res["direccion"]["estado"];
            $direccion->descripcion = strtoupper($res["direccion"]["descripcion"]);
            $direccion->fecha_modificacion = $fecha;
            $direccion->usuario_modificacion = $usuario_creacion;
            $direccion->activo = 1;
            $direccion->save();
            $serie = Serie::find($res["id_serie"]);
            $serie->serie = strtoupper($res["serie"]);
            $serie->fecha_modificacion = $fecha;
            $serie->usuario_modificacion = $usuario_creacion;
            $serie->activo = 1;
            $serie->save();
            return $this->crearRespuesta(1,"Serie dado de alta",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
}
