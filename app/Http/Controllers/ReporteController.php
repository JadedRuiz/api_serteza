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
        $detalle_contratacion = DB::table('rh_detalle_contratacion as dc')
        ->select(DB::raw('CONCAT(cc.nombre, " ",cc.apellido_paterno, " ", cc.apellido_materno) as nombre'), "cd.departamento","cp.puesto","dc.sueldo","dc.fecha_alta","dc.observacion","dc.id_departamento","ce.empresa","ccd.cliente","cu.nombre as usuario","cc.rfc","cc.curp","cc.numero_seguro","cc.correo","cdd.calle","cdd.numero_interior","cdd.numero_exterior","cdd.cruzamiento_uno","cdd.cruzamiento_dos","cdd.colonia","cdd.municipio","cdd.estado","cf.nombre as fotografia","cff.nombre as name_foto","cc.telefono","cc.telefono_dos")
        ->join("rh_movimientos as mc","mc.id_movimiento","=","dc.id_movimiento")
        ->join("gen_cat_cliente as ccd","ccd.id_cliente","=","mc.id_cliente")
        ->join("gen_cat_fotografia as cff","cff.id_fotografia","=","ccd.id_fotografia")
        ->join("gen_cat_empresa as ce","ce.id_empresa","=","dc.id_empresa")
        ->join("rh_cat_candidato as cc","cc.id_candidato","=","dc.id_candidato")
        ->join("gen_cat_direccion as cdd","cdd.id_direccion","=","cc.id_direccion")
        ->join("gen_cat_fotografia as cf","cf.id_fotografia","=","cc.id_fotografia")
        ->join("gen_cat_departamento as cd","cd.id_departamento","=","dc.id_departamento")
        ->join("gen_cat_puesto as cp","cp.id_puesto","=","dc.id_puesto")
        ->join("gen_cat_usuario as cu","cu.id_usuario","=","dc.usuario_creacion")
        ->where("dc.id_detalle_contratacion",$id_detalle)
        ->where("dc.activo",1)
        ->get();
        $detalle_contratacion[0]->name_foto = Storage::disk('cliente')->url($detalle_contratacion[0]->name_foto);
        $detalle_contratacion[0]->fotografia = Storage::disk('candidato')->url($detalle_contratacion[0]->fotografia);
        $pdf = PDF::loadView("reporte_contratado",compact('detalle_contratacion'))
        ->setPaper('A4');
        return $pdf->stream();
    }

    public function reporteContrato($id_movimiento)
    {
        $reporte_contrato = DB::table('rh_movimientos as mc')
        ->select("cc.cliente","cf.nombre as foto_cliente","mc.fecha_movimiento","mc.id_movimiento as folio","cu.nombre as usuario","cu.id_usuario as detalle")
        ->join("gen_cat_cliente as cc","cc.id_cliente","=","mc.id_cliente")
        ->join("gen_cat_fotografia as cf","cf.id_fotografia","=","cc.id_fotografia")
        ->join("gen_cat_usuario as cu","cu.id_usuario","=","mc.usuario_creacion")
        ->where("mc.id_movimiento",$id_movimiento)
        ->get();
        if(count($reporte_contrato)>0){
            $reporte_contrato[0]->detalle = [];
            $detalle = DB::table('rh_detalle_contratacion as dc')
            ->select("cc.nombre","cc.apellido_paterno","cc.apellido_materno","ce.empresa","dp.departamento","cp.puesto","dc.sueldo")
            ->join("rh_cat_candidato as cc","cc.id_candidato","=","dc.id_candidato")
            ->join("gen_cat_empresa as ce","ce.id_empresa","=","dc.id_empresa")
            ->join("gen_cat_departamento as dp","dp.id_departamento","=","dc.id_departamento")
            ->join("gen_cat_puesto as cp","cp.id_puesto","=","dc.id_puesto")
            ->where("dc.id_movimiento",$id_movimiento)
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
    public function reporteEmpleado($id_empleado,$id_empresa)
    {
        $reporte_empleado = DB::table('nom_empleados as ne')
        ->select("ne.id_empleado", DB::raw('CONCAT(rcc.nombre, " ", rcc.apellido_paterno, " ", rcc.apellido_materno) as nombre'),"rcc.telefono","rcc.telefono_dos","cf.nombre as fotografia","gcc.cliente","ns.sucursal","gcd.departamento","gcp.puesto","gcc.id_cliente as foto_empresa","gcc.id_cliente as empresa","rcc.rfc","rcc.curp","rcc.numero_seguro","rcc.correo","cdd.calle","cdd.numero_interior","cdd.numero_exterior","cdd.cruzamiento_uno","cdd.cruzamiento_dos","cdd.colonia","cdd.municipio","cdd.estado","ncn.nomina","scb.descripcion as banco","scs.tipocontrato","ne.fecha_ingreso","ne.fecha_antiguedad","ne.sueldo_diario","ne.sueldo_integrado","ne.sueldo_complemento")
        ->join("rh_cat_candidato as rcc","rcc.id_candidato","=","ne.id_candidato")
        ->join("gen_cat_direccion as cdd","cdd.id_direccion","=","rcc.id_direccion")
        ->join("gen_cat_fotografia as cf","cf.id_fotografia","=","rcc.id_fotografia")
        ->join("gen_cat_puesto as gcp","gcp.id_puesto","=","ne.id_puesto")
        ->join("gen_cat_departamento as gcd","gcd.id_departamento","=","gcp.id_departamento")
        ->join("nom_sucursales as ns","ns.id_sucursal","=","ne.id_sucursal")
        ->join("nom_cat_nomina as ncn","ncn.id_nomina","=","ne.id_nomina")
        ->join("gen_cat_cliente as gcc","gcc.id_cliente","=","ns.id_cliente")
        ->join("sat_catbancos as scb","scb.id_bancosat","=","ne.id_catbanco")
        ->join("sat_contratossat as scs","scs.id_contratosat","=","ne.id_contratosat")
        ->where("ne.id_empleado",$id_empleado)
        ->get();
        $empresa = DB::table('gen_cat_empresa as gce')
        ->select("cf.nombre as fotografia","gce.razon_social as empresa")
        ->join("gen_cat_fotografia as cf","cf.id_fotografia","=","gce.id_fotografia")
        ->where("gce.id_empresa",$id_empresa)
        ->get();
        if(count($reporte_empleado)>0){
            $reporte_empleado[0]->empresa = $empresa[0]->empresa;
            $reporte_empleado[0]->foto_empresa = Storage::disk('empresa')->url($empresa[0]->fotografia);
            $reporte_empleado[0]->fotografia = Storage::disk('candidato')->url($reporte_empleado[0]->fotografia);
            $pdf = PDF::loadView("reporte_empleado",compact('reporte_empleado'))
            ->setPaper('A4');
            return $pdf->stream(); 
        }else{
            return "NO SE HA PODIDO GENERAR EL PDF, POR FAVOR CONTACTE CON EL ADMINISTRADOR DEL SISTEMA";
        }
    }
}
