<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Candidato;
use App\Models\Cotizacion;
use App\Models\DetCotizacion;

class CostosController extends Controller
{
    public function costosNomina(Request $res)
    {
        try{

        
            $id_empresa = $res["id_empresa"];
            $ejercicio = $res["ejercicio"];
            $mes = $res["mes"];
            $id_periodo = $res["id_periodo"];

            $nomina = DB::table('nom_clientes_nomina as d')
            ->select("nomina","d.id_cliente_nomina", "d.id_nomina", "n.nomina", "d.id_cliente", "c.cliente", "d.id_estrategia", "e.estrategia", "d.descripcion", DB::raw('SUM(cn.neto_fiscal) AS neto_fiscal'), DB::raw('SUM(cn.neto_exento) AS neto_exento'),
                    DB::raw('SUM(cn.costo_social) AS costo_social'), DB::raw('SUM(cn.impuesto_estatal) AS impuesto_estatal'), DB::raw('SUM(cn.comision) AS comision'), DB::raw('SUM(cn.iva) AS iva'), DB::raw('SUM(cn.total) AS total'))
            ->join("nom_cifras_nomina as cn","d.id_cliente_nomina","=","cn.id_cliente_nomina")
            ->join("gen_cat_cliente as c","c.id_cliente","=","d.id_cliente")
            ->join("nom_cat_nomina as n","d.id_nomina","=","n.id_nomina")
            ->join("nom_cat_estrategia as e","d.id_estrategia","=","e.id_estrategia")
            ->join("nom_periodos as p","p.id_periodo","=","cn.id_periodo")
            ->where("d.id_empresa",$id_empresa)
            ->where("p.ejercicio",$ejercicio)
            ->where("p.mes",$mes);
            if($id_periodo != 0){
                $nomina = $nomina->where("cn.id_periodo",$id_periodo);
            }
            $nomina = $nomina
            ->groupby("d.id_cliente_nomina", "d.id_nomina", "n.nomina", "d.id_cliente", "c.cliente", "d.id_estrategia", "e.estrategia", "d.descripcion") 
            ->orderby("d.id_nomina")
            ->orderby("d.id_cliente")
            ->get();

                $suma_Fiscal = 0;
                $suma_Exento = 0;
                $suma_Costo = 0;
                $suma_Iestatal = 0;
                $suma_Comision = 0;
                $suma_Iva = 0;
                $suma_Total = 0;

                foreach($nomina as $nom){

                    $suma_Fiscal = $suma_Fiscal + round($nom->neto_fiscal, 2);
                    $suma_Exento = $suma_Exento + round($nom->neto_exento,2);
                    $suma_Costo = $suma_Costo + round($nom->costo_social,2);
                    $suma_Iestatal = $suma_Iestatal + round($nom->impuesto_estatal,2);
                    $suma_Comision = $suma_Comision + round($nom->comision,2);
                    $suma_Iva = $suma_Iva + round($nom->iva,2);
                    $suma_Total = $suma_Total + round($nom->total,2);
                }

                    
                return $this->crearRespuestaConTotales(1,$nomina,["neto_fiscal" => round($suma_Fiscal,2), "neto_exento" => round($suma_Exento,2), "costo_social" => round($suma_Costo,2), "impuesto_estatal" => round($suma_Iestatal,2), "comision" => round($suma_Comision,2), "iva" => round($suma_Iva,2), "total" => round($suma_Total,2)],200);
    
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    
}