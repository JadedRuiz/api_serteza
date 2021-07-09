<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class NominaController extends Controller
{
    public function obtenerNombreNominaPorId($id_nomina)
    {
        $nombre_nomina = DB::table('nom_cat_nomina')
        ->select("nomina")
        ->where("id_nomina",$id_nomina)
        ->get();
        if(count($nombre_nomina)>0){
            return $this->crearRespuesta(1,$nombre_nomina,200);
        }else{
            return $this->crearRespuesta(2,"Ha ocurrido un error",301);
        }
    }
    public function obtenerLigaEmpresaNomina(Request $res)
    {
        $id_empresa = $res["id_empresa"];
        $id_status = $res["id_status"];
        $str = "=";
        if($id_status == "-1"){
            $str = "!=";
        }
        $recuperarInfoNomina = DB::table('liga_empresa_nomina as len')
        ->select("len.id_empresa_nomina","ncn.nomina","len.activo as id_status","len.activo","ncn.id_nomina")
        ->join("nom_cat_nomina as ncn","ncn.id_nomina","=","len.id_nomina")
        ->where("id_empresa",$id_empresa)
        ->where("len.activo",$str,$id_status)
        ->get();
        if(count($recuperarInfoNomina)){
            foreach($recuperarInfoNomina as $nomina){
                if($nomina->id_status == "1"){
                    $nomina->id_status = "Activo"; 
                }
                if($nomina->id_status == "0"){
                    $nomina->id_status = "Inactivo"; 
                }
            }
            return $this->crearRespuesta(1,$recuperarInfoNomina,200);
        }else{
            return $this->crearRespuesta(2,"No se tiene nominas",200);
        }
    }
    public function insertarLigaNominaEmpresa(Request $res)
    {
        try{
            $id_nomina = $res["id_nomina"];
            $id_empresa = $res["id_empresa"];
            $fecha = $this->getHoraFechaActual();
            $usuario_creacion = $res["usuario_creacion"];
            $id_empresa_nomina = $this->getSigId("liga_empresa_nomina");
            DB::insert('insert into liga_empresa_nomina (id_empresa_nomina, id_empresa, id_nomina, fecha_creacion, usuario_creacion, activo) values (?,?,?,?,?,?)', [$id_empresa_nomina,$id_empresa,$id_nomina,$fecha,$usuario_creacion,1]);
            return $this->crearRespuesta(1,"Se ha agreado el tipo de nómin a la empresa",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function eliminarLigaEmpresaNomina($id_empresa_nomina)
    {
        try{
            DB::update('update liga_empresa_nomina set activo = 0 where id_empresa_nomina = ?', [$id_empresa_nomina]);
            return $this->crearRespuesta(1,"Se ha eliminado el tipo de nómina a la empresa",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function activarLigaEmpresaNomina($id_empresa_nomina)
    {
        try{
            DB::update('update liga_empresa_nomina set activo = 1 where id_empresa_nomina = ?', [$id_empresa_nomina]);
            return $this->crearRespuesta(1,"Se ha activado el tipo de nómina a la empresa",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
}
