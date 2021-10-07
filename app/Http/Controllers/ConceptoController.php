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
    public function obtenerConceptosPorIdEmpleado($id_empleado,$id_empresa,$id_periodo)
    {
        $conceptos = DB::table('nom_conceptos as nomc')
        ->select("nomc.id_concepto","nomc.concepto", "nomc.concepto as unidad", "nomc.concepto as importe", "nomc.concepto as saldo", "nomc.concepto as ajuste", "nomc.id_tipoconcepto","nomc.automatico","nomc.utiliza_unidade","nomc.utiliza_importe","nomc.utiliza_saldo","nomc.folio")
        ->where("id_empresa",$id_empresa)
        ->orderBy("folio","asc")
        ->get();
        $conceptos_del_empleado = DB::table('nom_movnomina as nmn')
        ->select("nmn.id_concepto","nomc.concepto","nmn.unidad","nmn.importe","nmn.saldo","nmn.ajuste","nomc.utiliza_unidade","nomc.utiliza_importe","nomc.utiliza_saldo", "nomc.folio")
        ->join("nom_conceptos as nomc","nomc.id_concepto","=","nmn.id_concepto")
        ->where("id_empleado",$id_empleado)
        ->where("id_periodo",$id_periodo)
        ->where("nmn.activo",1)
        ->orderBy("folio","asc")
        ->get();
        if(count(($conceptos_del_empleado))>0){
            foreach($conceptos_del_empleado as $concepto){
                if($concepto->utiliza_unidade == "1"){
                    $concepto->utiliza_unidade = false;
                }else{
                    $concepto->utiliza_unidade = true;
                }
                if($concepto->utiliza_saldo == "1"){
                    $concepto->utiliza_saldo = false;
                }else{
                    $concepto->utiliza_saldo = true;
                }
                if($concepto->utiliza_importe == "1"){
                    $concepto->utiliza_importe = false;
                }else{
                    $concepto->utiliza_importe = true;
                }
                $concepto->importe = $concepto->importe;
            }
            return $this->crearRespuesta(1,$conceptos_del_empleado,200);
        }
        if(count($conceptos)==0){
            return $this->crearRespuesta(2,"No se tiene conceptos capturados",301);
        }else{
            //Recorrecomes los conceptos automaticos
            foreach($conceptos as $concepto){
                if($concepto->utiliza_unidade == "1"){
                    $concepto->utiliza_unidade = false;
                }else{
                    $concepto->utiliza_unidade = true;
                }
                if($concepto->utiliza_saldo == "1"){
                    $concepto->utiliza_saldo = false;
                }else{
                    $concepto->utiliza_saldo = true;
                }
                if($concepto->utiliza_importe == "1"){
                    $concepto->utiliza_importe = false;
                }else{
                    $concepto->utiliza_importe = true;
                }
                if($concepto->automatico == 1){
                    $tipo_concepto = DB::table('nom_tipoconceptos')
                    ->select("tipo_concepto")
                    ->where("id_tipoconceptos",$concepto->id_tipoconcepto)
                    ->first()->tipo_concepto;
                    if($tipo_concepto == "SUELDOS Y SALARIOS"){
                        $datos_nomina = DB::table('nom_empleados as ne')
                        ->select("ncn.dias_contables","ne.sueldo_complemento")
                        ->join("nom_cat_nomina as ncn","ncn.id_nomina","=","ne.id_nomina")
                        ->where("id_empleado",$id_empleado)
                        ->first();
                        $concepto->unidad = $datos_nomina->dias_contables;
                        $concepto->importe = 0;
                        if($datos_nomina->sueldo_complemento > 0){
                            $concepto->importe = number_format($datos_nomina->sueldo_complemento / $datos_nomina->dias_contables,2,".",",");
                        }
                        $concepto->saldo = 0;
                        $concepto->ajuste = 0;
                    }
                }else{
                    $concepto->unidad = 0;
                    $concepto->importe = 0;
                    $concepto->saldo = 0;
                    $concepto->ajuste = 0;
                }
            }
            return $this->crearRespuesta(1,$conceptos,200);
        }
    }
    public function obtenerConcpetosPorId($id_empresa)
    {
        $conceptos = DB::table('nom_conceptos as nc')
        ->select("folio","id_concepto", "concepto", "utiliza_unidade", "utiliza_importe", "utiliza_saldo")
        ->where("id_empresa",$id_empresa)
        ->where("automatico",0)
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
                $usuario = $res["usuario"];
                $fecha = $this->getHoraFechaActual();
                DB::insert('insert into nom_conceptos (id_empresa, id_tipoconcepto, id_conceptosat, concepto, tipo, utiliza_unidade, utiliza_importe, utiliza_saldo, seincrementa, imprime_saldo, automatico, imprimir, parametro1, parametro2, cuentacontable, impuesto_estatal, especie, usuario_creacion, fecha_creacion, activo) values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [$id_empresa,$id_concepto,$id_concepto_sat,$concepto,$tipo,$unidades,$importe,$saldo,$incrementa,$imprime,$automatico,$imprimir,$parametro_uno,$parametro_dos,$cuenta_contable,$impuesto_estatal,$especie,$usuario,$fecha,1]);
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
    public function obtenerConceptoPorIdMovNomina($id_movnomina)
    {
        $concepto = DB::table('nom_movnomina')
        ->select("saldo","importe","ajuste","unidad","id_concepto")
        ->where("id_movnomina",$id_movnomina)
        ->where("activo",1)
        ->get();
        if(count($concepto)>0){
            return $this->crearRespuesta(1,$concepto,200);
        }else{
            return $this->crearRespuesta(2,"No se ha encontrado los datos del concepto",200);
        }
    }
    public function altaConceptoAEmpleado(Request $res)
    {
        try{
            $id_empleado = $res["id_empleado"];
            $id_periodo = $res["id_periodo"];
            $conceptos = $res["conceptos"];
            $usuario = $res["usuario"];
            $fecha = $this->getHoraFechaActual();
            //Validamos los datos requeridos
            if($id_empleado == ""){
                return $this->crearRespuesta(2,"No se cuenta con el empleado",200);
            }
            if($id_periodo == ""){
                return $this->crearRespuesta(2,"No se cuenta con el periodo",200);
            }
            foreach($conceptos as $concepto){
                $id_concepto = $concepto["id_concepto"];
                $unidad = $this->formatearCampo($concepto["unidades"],null);
                $importe = $this->formatearCampo($concepto["importe"],"$");
                $saldo = $this->formatearCampo($concepto["saldo"],"$");
                $ajuste = $this->formatearCampo($concepto["ajuste"],"$");
                if($importe == ""){
                    $importe = "0";
                }
                if($saldo == ""){
                    $saldo = "0";
                }
                if($unidad == ""){
                    $unidad = "0";
                }
                if($ajuste == ""){
                    $ajuste = "0";
                }
                //Obtener los parametros del concepto
                $parametro_uno = "0";
                $parametro_dos = "0";
                $parametros = DB::table('nom_conceptos')
                ->select("parametro1","parametro2")
                ->where("id_concepto",$id_concepto)
                ->get();
                if(count($parametros)>0){
                    if($parametros[0]->parametro1 != ""){
                        $parametro_uno = $parametros[0]->parametro1;
                    }
                    if($parametros[0]->parametro2 != ""){
                        $parametro_dos = $parametros[0]->parametro2;
                    }
                }
                //Validamos si el empleado  ya tiene el concepto en el periodo
                $validar = DB::table('nom_movnomina')
                ->select("id_movnomina","activo")
                ->where("id_concepto",$id_concepto)
                ->where("id_periodo",$id_periodo)
                ->where("id_empleado",$id_empleado)
                ->get();
                if(count($validar) > 0){
                    DB::update('update nom_movnomina set activo = ?, unidad = ?, importe = ?, saldo = ?, ajuste = ?, usuario_modificacion = ?, fecha_modificacion = ? where id_movnomina = ?', [1,$unidad,$importe,$saldo,$ajuste,$usuario,$fecha,$validar[0]->id_movnomina]);
                }else{
                    DB::insert('insert into nom_movnomina (id_empleado, id_periodo, id_concepto, unidad, importe, saldo, ajuste, parametro1, parametro2, usuario_creacion, fecha_creacion, activo) values (?,?,?,?,?,?,?,?,?,?,?,?)', [$id_empleado,$id_periodo,$id_concepto,$unidad,$importe,$saldo,$ajuste,$parametro_uno,$parametro_dos,$usuario,$fecha,1]);
                }
            }
            return $this->crearRespuesta(1,"Se ha insertado correctamente el concepto",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function modificarConceptoAEmpleado(Request $res)
    {
        try{
            $id_movnomina = $res["id_concepto"];
            $unidad = $res["concepto"]["unidades"];
            $importe = $res["concepto"]["importe"];
            $saldo = $res["concepto"]["saldo"];
            $ajuste = $res["concepto"]["ajuste"];
            $usuario = $res["usuario"];
            if($importe == ""){
                $importe = "0";
            }
            if($saldo == ""){
                $saldo = "0";
            }
            if($unidad == ""){
                $unidad = "0";
            }
            if($ajuste == ""){
                $ajuste = "0";
            }
            $fecha = $this->getHoraFechaActual();
            DB::update('update nom_movnomina set unidad = ?, importe = ?, saldo = ?, ajuste = ?, usuario_modificacion = ?, fecha_modificacion = ? where id_movnomina = ?', [$unidad,$importe,$saldo,$ajuste,$usuario,$fecha,$id_movnomina]);
            return $this->crearRespuesta(1,"Se ha actualizado el concepto del empleado",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function eliminarConceptoAEmpleado(Request $res)
    {
        try{
            $usuario = $res["usuario"];
            $id_movnomina = $res["id_concepto"];
            $fecha = $this->getHoraFechaActual();
            DB::update('update nom_movnomina set activo = 0, usuario_modificacion = ?, fecha_modificacion = ? where id_movnomina = ?', [$usuario,$fecha,$id_movnomina]);
            return $this->crearRespuesta(1,"Se ha eliminado el concepto del empleado",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
}
