<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Storage;

class ReporteController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function reporteContratado($id_detalle)
    {
        $detalle_contratacion = DB::table('detalle_contratacion as dc')
        ->select("cc.nombre","cc.apellido_paterno","cc.apellido_materno", "cd.departamento","cp.puesto","dc.sueldo","dc.fecha_alta","dc.observacion","dc.id_departamento","ce.empresa","ccd.cliente","cu.nombre as usuario","cc.rfc","cc.curp","cc.numero_seguro","cc.correo","cdd.calle","cdd.numero_interior","cdd.numero_exterior","cdd.cruzamiento_uno","cdd.cruzamiento_dos","cdd.colonia","cdd.municipio","cdd.estado","cf.extension","cf.fotografia","cff.nombre as name_foto")
        ->join("mov_contratacion as mc","mc.id_contratacion","=","dc.id_contratacion")
        ->join("cat_cliente as ccd","ccd.id_cliente","=","mc.id_cliente")
        ->join("cat_fotografia as cff","cff.id_fotografia","=","ccd.id_fotografia")
        ->join("cat_empresa as ce","ce.id_empresa","=","dc.id_empresa")
        ->join("cat_candidato as cc","cc.id_candidato","=","dc.id_candidato")
        ->join("cat_direccion as cdd","cdd.id_direccion","=","cc.id_direccion")
        ->join("cat_fotografia as cf","cf.id_fotografia","=","cc.id_fotografia")
        ->join("cat_departamento as cd","cd.id_departamento","=","dc.id_departamento")
        ->join("cat_puesto as cp","cp.id_puesto","=","dc.id_puesto")
        ->join("cat_usuario as cu","cu.id_usuario","=","dc.usuario_creacion")
        ->where("dc.id_detalle_contratacion",$id_detalle)
        ->where("dc.activo",1)
        ->get();
        $detalle_contratacion[0]->name_foto = Storage::disk('cliente')->url($detalle_contratacion[0]->name_foto);
        $pdf = PDF::loadView("reporte_contratado",compact('detalle_contratacion'))
        ->setPaper('A4');
        return $pdf->stream();
    }

    public function reporteContrato($id_contratacion)
    {
        $reporte_contrato = DB::table('mov_contratacion as mc')
        ->select("cc.cliente","cf.nombre as foto_cliente","mc.fecha_contratacion","mc.id_contratacion as folio","cu.nombre as usuario","cu.id_usuario as detalle")
        ->join("cat_cliente as cc","cc.id_cliente","=","mc.id_cliente")
        ->join("cat_fotografia as cf","cf.id_fotografia","=","cc.id_fotografia")
        ->join("cat_usuario as cu","cu.id_usuario","=","mc.usuario_creacion")
        ->where("mc.id_contratacion",$id_contratacion)
        ->get();
        if(count($reporte_contrato)>0){
            $reporte_contrato[0]->detalle = [];
            $detalle = DB::table('detalle_contratacion as dc')
            ->select("cc.nombre","cc.apellido_paterno","cc.apellido_materno","ce.empresa","dp.departamento","cp.puesto","dc.sueldo")
            ->join("cat_candidato as cc","cc.id_candidato","=","dc.id_candidato")
            ->join("cat_empresa as ce","ce.id_empresa","=","dc.id_empresa")
            ->join("cat_departamento as dp","dp.id_departamento","=","dc.id_departamento")
            ->join("cat_puesto as cp","cp.id_puesto","=","dc.id_puesto")
            ->where("dc.id_contratacion",$id_contratacion)
            ->get();
            if(count($detalle)>0){
                foreach($detalle as $trabajador){
                    array_push($reporte_contrato[0]->detalle,$trabajador);
                }
            }
            $reporte_contrato[0]->foto_cliente = Storage::disk('cliente')->url($reporte_contrato[0]->foto_cliente);
            $pdf = PDF::loadView("reporte_contrato",compact('reporte_contrato'))
            ->setPaper('A4');
            return $pdf->stream(); 
        }else{
            return "NO SE HA PODIDO GENERAR EL PDF, POR FAVOR CONTACTE CON EL ADMINISTRADOR DEL SISTEMA";
        }
        
    }
}
