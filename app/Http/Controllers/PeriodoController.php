<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PeriodoController extends Controller
{
    public function obtenerFechaFinalDelEjercicioAnt($anio,$id_empresa,$id_nomina)
    {
        $validar = DB::table('nom_periodos')
        ->where("ejercicio",$anio)
        ->where("id_empresa",$id_empresa)
        ->where("id_nomina",$id_nomina)
        ->where("ejercicio",$anio)
        ->get()
        ->count();
        if($validar>0){
            return $this->crearRespuesta(2,"Ya existe esté ejercicio",200);
        }else{
            $utlimo_ejercicio = DB::table('nom_periodos')
            ->select("fecha_final","ejercicio")
            ->where("id_empresa",$id_empresa)
            ->where("id_nomina",$id_nomina)
            ->orderBy("periodo","DESC")
            ->get();
            if(count($utlimo_ejercicio)>0){
                if($utlimo_ejercicio[0]->ejercicio > $anio){
                    return $this->crearRespuesta(1,["first" => true],200);
                }
                $utlimo_ejercicio[0]->fecha_final = date("m/d/Y", strtotime($utlimo_ejercicio[0]->fecha_final));

                return $this->crearRespuesta(1,["first"=>false,"data"=>$utlimo_ejercicio[0]],200);
            }else{
                return $this->crearRespuesta(1,["first" => true],200);
            }
        }
    }
    public function obtenerPeriodos(Request $res){
        $id_empresa = $res["id_empresa"];
        $id_nomina = $res["id_nomina"];
        $palabra = "%".$res["palabra"]."%";
        $ejercicios_de_nomina = DB::table('nom_periodos')
        ->select("ejercicio")
        ->where("id_nomina",$id_nomina)
        ->where("id_empresa",$id_empresa)
        ->where("ejercicio","like",$palabra)
        ->distinct()
        ->get();
        $respuesta = [];
        $cont = 1;
        foreach($ejercicios_de_nomina as $ejercicio){
            $periodos =  DB::table('nom_periodos')
            ->select("id_periodo","ejercicio","fecha_inicial","fecha_final")
            ->where("id_nomina",$id_nomina)
            ->where("id_empresa",$id_empresa)
            ->where("ejercicio",$ejercicio->ejercicio)
            ->get();
            $fecha_inicial = "";
            $fecha_final = "";
            for($i=0;$i<count($periodos);$i++){
                if($i == 0){
                    $fecha_inicial = $periodos[$i]->fecha_inicial;
                }
                if($i == (count($periodos)-1)){
                    $fecha_final = $periodos[$i]->fecha_final;
                }
            }
            array_push($respuesta,[
                "id_periodo" => $cont,
                "ejercicio" => $periodos[0]->ejercicio,
                "fecha_inicial" => date("m/d/Y", strtotime($fecha_inicial)),
                "fecha_final" => date("m/d/Y", strtotime($fecha_final)),
            ]);
            $cont++;
        }
        if(count($respuesta)>0){
            return $this->crearRespuesta(1,$respuesta,200);
        }else{
            return $this->crearRespuesta(2,"Ha ocurrido un error",301);
        }
    }
    public function crearNuevoPeriodo(Request $res)
    {
        try{
            $id_empresa = $res["id_empresa"];
            $id_nomina = $res["id_nomina"];
            $ejercicio = $res["ejercicio"];
            $validar = DB::table('nom_periodos')
            ->where("id_empresa",$id_empresa)
            ->where("ejercicio",$ejercicio)
            ->where("id_nomina",$id_nomina)
            ->get()
            ->count();
            if($validar > 0){
                return $this->crearRespuesta(2,"Este ejericio ya existe",200);
            }
            $usuario = $res["usuario"];
            $fecha = $this->getHoraFechaActual();
            foreach($res["periodo_array"] as $periodo){
                $id_periodo = $periodo["id_periodo"];
                $actual = $periodo["actual"];
                $cerrado = $periodo["cerrado"];
                $fecha_inicial = date("Y/m/d", strtotime($periodo["fecha_inicial"]));
                $fecha_final = date("Y/m/d", strtotime($periodo["fecha_final"]));
                $septimo_dia = $periodo["septimo"];
                $mes = intval($periodo["mes"]);
                $inicio_mes = $periodo["inicio_mes"];
                $fin_de_mes = $periodo["fin_de_mes"];
                $ejericio_inicial = $periodo["ejercicio_inicial"];
                $ejericio_final = $periodo["ejercicio_inicial"];
                $fecha_poliza = date("Y/m/d", strtotime($periodo["fecha_poliza"]));
                $dias_desface = $periodo["dias_desface"];
                $timbrado = $periodo["timbrado"];
                DB::insert('insert into nom_periodos (id_empresa, id_nomina, periodo, ejercicio, actual, cerrado, fecha_inicial, fecha_final, septimo_dia, mes, esiniciomes, esfinmes, esejercicioinicial, esejerciciofinal, fecha_poliza, dias_desface, timbrado, usuario_creacion, fecha_creacion) values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [$id_empresa, $id_nomina, $id_periodo, $ejercicio, $actual, $cerrado, $fecha_inicial, $fecha_final, $septimo_dia, $mes, $inicio_mes, $fin_de_mes, $ejericio_inicial, $ejericio_final, $fecha_poliza, $dias_desface, $timbrado, $usuario, $fecha]);
            }
            return $this->crearRespuesta(1,"Periodo insertado con exito",200);

        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
}
