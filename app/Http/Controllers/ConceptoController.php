<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConceptoController extends Controller
{
    public function autocomplete(Request $res)
    {
        $id_empresa = $res["id_empresa"];
        $palabra = "%".strtoupper($res["palabra"])."%";
        $conceptos = DB::table('nom_conceptos as nc')
        ->select("folio","id_concepto", "concepto", "utiliza_unidade", "utiliza_importe", "utiliza_saldo")
        ->where("id_empresa",$id_empresa)
        ->where("activo",1)
        ->where(function ($query) use ($palabra){
            $query->orWhere("concepto", "like", $palabra)
            ->orWhere("id_concepto", "like", $palabra);
        })
        ->get();
        if(count($conceptos)>0){
            return $this->crearRespuesta(1,$conceptos,200);
        }else{
            return $this->crearRespuesta(2,"No se han encontrado resultados",200);
        }
    }
    public function obtenerConcpetosPorId($id_empresa)
    {
        $conceptos = DB::table('nom_conceptos as nc')
        ->select("folio","id_concepto", "concepto", "utiliza_unidade", "utiliza_importe", "utiliza_saldo")
        ->where("id_empresa",$id_empresa)
        ->where("activo",1)
        ->get();
        if(count($conceptos)>0){
                return $this->crearRespuesta(1,$conceptos,200);
            }else{
                return $this->crearRespuesta(2,"No se han encontrado conceptos en esta empresa",200);
            }
    }
    public function obtenerConcpetosPorIdConcepto($id_concepto)
    {
        $concepto = DB::table('nom_conceptos as nc')
        ->select("id_tipoconcepto", "id_conceptosat", "concepto", "tipo", "utiliza_unidade", "utiliza_importe", "utiliza_saldo", "seincrementa", "automatico", "imprimir", "parametro1", "parametro2", "cuentacontable", "impuesto_estatal", "especie")
        ->where("id_concepto",$id_concepto)
        ->first();
        if($concepto){
                return $this->crearRespuesta(1,$concepto,200);
            }else{
                return $this->crearRespuesta(2,"No se ha encontrado el concepto",200);
            }
    }
    public function crearConcepto(Request $res)
    {
            try{
                $id_empresa = $res["id_empresa"];
                $id_concepto_sat = $res["tipo_concepto_sat"];
                $id_concepto = $res["tipo_concepto"];
                $concepto = strtoupper($res["concepto"]);
                $tipo = $res["tipo"];
                $unidades = $res["unidades"];
                $importe = $res["importe"];
                $saldo = $res["saldo"];
                $incrementa = $res["incrementable"];
                $imprime = $res["imprime"];
                $automatico = $res["automatico"];
                $imprimir = $res["imprimir"];
                $parametro_uno = $res["parametro_uno"];
                $parametro_dos = $res["parametro_dos"];
                $cuenta_contable = $res["cuenta_contable"];
                $impuesto_estatal = $res["impuesto_estatal"];
                $especie = $res["especie"];
                $fecha = $this->getHoraFechaActual();
                DB::insert('insert into nom_conceptos (id_empresa, id_tipoconcepto, id_conceptosat, concepto, tipo, utiliza_unidade, utiliza_importe, utiliza_saldo, seincrementa, imprime_saldo, automatico, imprimir, parametro1, parametro2, cuentacontable, impuesto_estatal, especie, fecha_creacion,activo) values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [$id_empresa,$id_concepto,$id_concepto_sat,$concepto,$tipo,$unidades,$importe,$saldo,$incrementa,$imprime,$automatico,$imprimir,$parametro_uno,$parametro_dos,$cuenta_contable,$impuesto_estatal,$especie,$fecha,1]);
                return $this->crearRespuesta(1,"El concepto ha sido guardado éxitosamente",200);
            }catch(Throwable $e){
                return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
            }
    }
    public function modificarConcepto(Request $res)
    {
        try{
            $id_concepto = $res["id_concepto"];
                $id_concepto_sat = $res["tipo_concepto_sat"];
                $id_tipo_concepto = $res["tipo_concepto"];
                $concepto = strtoupper($res["concepto"]);
                $tipo = $res["tipo"];
                $unidades = $res["unidades"];
                $importe = $res["importe"];
                $saldo = $res["saldo"];
                $incrementa = $res["incrementable"];
                $imprime = $res["imprime"];
                $automatico = $res["automatico"];
                $imprimir = $res["imprimir"];
                $parametro_uno = $res["parametro_uno"];
                $parametro_dos = $res["parametro_dos"];
                $cuenta_contable = $res["cuenta_contable"];
                $impuesto_estatal = $res["impuesto_estatal"];
                $especie = $res["especie"];
                $fecha = $this->getHoraFechaActual();
                DB::update('update nom_conceptos set id_tipoconcepto = ?, id_conceptosat = ?, concepto = ?, tipo = ?, utiliza_unidade = ?, utiliza_importe = ?, utiliza_saldo = ?, seincrementa = ?, imprime_saldo = ?, automatico = ?, imprimir = ?, parametro1 = ?, parametro2 = ?, cuentacontable = ?, impuesto_estatal = ?, especie = ?, fecha_modificacion = ? where id_concepto = ?', [$id_tipo_concepto,$id_concepto_sat,$concepto,$tipo,$unidades,$importe,$saldo,$incrementa,$imprime,$automatico,$imprimir,$parametro_uno,$parametro_dos,$cuenta_contable,$impuesto_estatal,$especie,$fecha,$id_concepto]);
                return $this->crearRespuesta(1,"El concepto ha sido modificado éxitosamente",200);
                $especie = $res["especie"];

            }catch(Throwable $e){
                return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
            }
    }
    public function cambiarActivo($id_concepto,$activo)
    {
            try{
                DB::update('update nom_conceptos set activo = ? where id_concepto = ?', [$activo,$id_concepto]);
                return $this->crearRespuesta(1,"El concepto ha sido modificado éxitosamente",200);
            }catch(Throwable $e){
                return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
            }
    }
}
