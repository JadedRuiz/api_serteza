<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PeriodoController extends Controller
{
    public function obtenerPeriodoEjercicioActual($id_empresa,$id_nomina)
    {
        $ano_actual = date("Y");
        $periodo_actual = DB::table('nom_periodos')
        ->select("fecha_inicial","fecha_final")
        ->where("id_empresa",$id_empresa)
        ->where("ejercicio",$ano_actual)
        ->where("id_nomina",$id_nomina)
        ->where("actual",1)
        ->first();
        if($periodo_actual){
            $periodo_actual->fecha_inicial = date("d-m-Y",strtotime($periodo_actual->fecha_inicial));
            $periodo_actual->fecha_final = date("d-m-Y",strtotime($periodo_actual->fecha_final));
            return $this->crearRespuesta(1,$periodo_actual,200);
        }else{
            return $this->crearRespuesta(2,"No existe un periodo actual para el ejercicio"." ".$ano_actual,200);
        }
    }
    public function obtenerFechaFinalDelEjercicioAnt($anio,$id_empresa,$id_nomina)
    {
        $validar = DB::table('nom_periodos')
        ->where("ejercicio",$anio)
        ->where("id_empresa",$id_empresa)
        ->where("id_nomina",$id_nomina)
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
        $palabra = "%".$res["palabra"]."%";
        $id_nomina = $res["id_nomina"];
        $ejercicios_de_nomina = DB::table('nom_periodos')
        ->select("ejercicio","nom_periodos.id_nomina")
        ->join("nom_cat_nomina as ncn","ncn.id_nomina","=","nom_periodos.id_nomina")
        ->where("id_empresa",$id_empresa)
        ->where(function ($query) use ($palabra){
            $query->orWhere("ejercicio","like",$palabra)
            ->orWhere("ncn.nomina", "like", $palabra);
        })
        ->distinct()
        ->get();
        $respuesta = [];
        $cont = 1;
        foreach($ejercicios_de_nomina as $ejercicio){
            $periodos =  DB::table('nom_periodos')
            ->select("id_periodo","ejercicio","fecha_inicial","fecha_final","ncn.nomina","ncn.id_nomina")
            ->join("nom_cat_nomina as ncn","ncn.id_nomina","=","nom_periodos.id_nomina")
            ->where("id_empresa",$id_empresa)
            ->where("nom_periodos.id_nomina",$ejercicio->id_nomina)
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
                "id_real" => $periodos[0]->id_periodo,
                "nomina" => $periodos[0]->nomina,
                "ejercicio" => $periodos[0]->ejercicio,
                "fecha_inicial" => date("m/d/Y", strtotime($fecha_inicial)),
                "fecha_final" => date("m/d/Y", strtotime($fecha_final)),
            ]);
            $cont++;
        }
        if(count($respuesta)>0){
            return $this->crearRespuesta(1,$respuesta,200);
        }else{
            return $this->crearRespuesta(2,"Ha ocurrido un error",200);
        }
    }
    public function obtenerPeriodoPorId($id_periodo)
    {
        $periodo = DB::table('nom_periodos')
        ->select("ncn.id_nomina","id_empresa","ejercicio","ncn.nomina")
        ->join("nom_cat_nomina as ncn","ncn.id_nomina","=","nom_periodos.id_nomina")
        ->where("id_periodo",$id_periodo)
        ->first();
        $respuesta = [
            "id_nomina" => $periodo->id_nomina,
            "id_empresa" => $periodo->id_empresa,
            "ejercicio" => $periodo->ejercicio,
            "periodo_array" => array()
        ];
        if($periodo){
            $periodos = DB::table('nom_periodos')
            ->select("periodo as id_periodo","mes","mes as mes_completo","fecha_inicial","fecha_final","actual","cerrado","timbrado","esiniciomes as inicio_mes","esfinmes as fin_de_mes","dias_desface","fecha_poliza","septimo_dia as septimo","esejercicioinicial as ejercicio_inicial","esejerciciofinal as ejercicio_final")
            ->where("id_nomina",$periodo->id_nomina)
            ->where("id_empresa",$periodo->id_empresa)
            ->where("ejercicio",$periodo->ejercicio)
            ->get();
            if(count($periodos)>0){
                $cont = 0;
                foreach($periodos as $periodo_row){
                    $periodo_row->fecha_inicial = date("m/d/Y",strtotime($periodo_row->fecha_inicial));
                    $periodo_row->fecha_final = date("m/d/Y",strtotime($periodo_row->fecha_final));
                    $periodo_row->mes_completo = $this->replace_mes($periodo_row->mes_completo);
                }
                array_push($respuesta["periodo_array"],$periodos);
                return $this->crearRespuesta(1,$respuesta,200);
            }else{
                return $this->crearRespuesta(2,"Este ejercicio no cuenta con periodos",200);
            }
        }else{
            return $this->crearRespuesta(2,"Este ejercicio está dañado",200);
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
    public function modificarPeriodo(Request $res)
    {
        try{
            $id_empresa = $res["id_empresa"];
            $id_nomina = $res["id_nomina"];
            $ejercicio = $res["ejercicio"];
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
                DB::update('update nom_periodos set actual = ?, cerrado = ?, fecha_inicial = ?, fecha_final = ?, septimo_dia = ?, mes = ?, esiniciomes = ?, esfinmes = ?, esejercicioinicial = ?, esejerciciofinal = ?, fecha_poliza = ?, dias_desface = ?, timbrado = ?, usuario_modificacion = ?, fecha_modificacion = ?  where id_empresa = ? and id_nomina = ? and ejercicio  = ? and periodo = ?', [$actual, $cerrado, $fecha_inicial, $fecha_final, $septimo_dia, $mes, $inicio_mes, $fin_de_mes, $ejericio_inicial, $ejericio_final, $fecha_poliza, $dias_desface, $timbrado, $usuario, $fecha,$id_empresa, $id_nomina, $ejercicio, $id_periodo]);
            }
            return $this->crearRespuesta(1,"Periodo modificado con exito",200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function replace_mes($mes)
    {
        switch($mes){
            case 1 :
              return "Enero";
            case 2 : 
              return "Febrero";
            case 3 :
              return "Marzo";
            case 4 :
              return "Abril";
            case 5 :
              return "Mayo";
            case 6 :
              return "Junio";
            case 7 :
              return "Julio";
            case 8 :
              return "Agosto";
            case 9 :
              return "Septiembre";
            case 10 :
              return "Octubre";
            case 11 :
              return "Noviembre";
            case 12 :
              return "Diciembre";
          }
    }
}
