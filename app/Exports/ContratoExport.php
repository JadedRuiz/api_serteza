<?php

namespace App\Exports;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;
use App\Exports\NumerosEnLetras;

class ContratoExport
{
    public function obtenerContrato($id_puesto, $id_mov)
    {
        if($id_puesto == -1){
            return $this->contratoGenerico($id_mov);
        }
    }

    public function contratoGenerico($id_mov)
    {
        try{
            $detalle_contratacion = DB::table('rh_detalle_movimiento as dc')
            ->select("dc.id_detalle",DB::raw('CONCAT(cc.apellido_paterno," ",cc.apellido_materno, " ",cc.nombre) as nombre'), "cc.curp", "cc.rfc", "cd.departamento","cp.puesto","dc.sueldo", "dc.sueldo_neto", "dc.fecha_detalle","dc.observacion","cc.id_candidato","cd.id_departamento","dc.id_puesto","ce.empresa","ce.rfc as rfcempresa","dc.id_nomina","rm.id_status","dc.id_sucursal", "ns.sucursal","ncn.nomina",DB::raw("CONCAT(gcd.calle,' #',gcd.numero_exterior, ' ,', gcd.cruzamiento_uno, ' ', gcd.cruzamiento_dos) as calleempresa"),DB::raw("CONCAT(gcd_dos.calle,' #',gcd_dos.numero_exterior, ' ,', gcd_dos.cruzamiento_uno, ' ', gcd_dos.cruzamiento_dos) as calle"))
            ->join("rh_movimientos as rm","rm.id_movimiento","=","dc.id_movimiento")
            ->join("rh_cat_candidato as cc","cc.id_candidato","=","dc.id_candidato")
            ->join("gen_cat_puesto as cp","cp.id_puesto","=","dc.id_puesto")
            ->join("gen_cat_departamento as cd","cd.id_departamento","=","cp.id_departamento")
            ->join("gen_cat_empresa as ce","ce.id_empresa","=","cd.id_empresa")
            ->join("nom_cat_nomina as ncn","ncn.id_nomina","=","dc.id_nomina")
            ->join("nom_sucursales as ns","ns.id_sucursal","=","dc.id_sucursal")
            ->leftJoin("gen_cat_direccion as gcd","ce.id_direccion","=","gcd.id_direccion")
            ->leftJoin("gen_cat_direccion as gcd_dos","cc.id_direccion","=","gcd_dos.id_direccion")
            ->where("dc.id_detalle",$id_mov)
            ->where("dc.activo",1)
            ->first();
            $file = storage_path('contratos');
            $phpword = new TemplateProcessor($file."\CONTRATO GENERICO.docx");
            $phpword->setValue('REPRESENTANTE','');
            $phpword->setValue('EMPRESA',$detalle_contratacion->empresa);
            $phpword->setValue('EMPLEADO', $detalle_contratacion->nombre);
            $phpword->setValue('DOMICILIOEMPRESA',$detalle_contratacion->calleempresa);
            $phpword->setValue('RFCEMPRESA',strtoupper($detalle_contratacion->rfcempresa));
            $phpword->setValue('CURP',strtoupper($detalle_contratacion->curp));
            $phpword->setValue('RFC',strtoupper($detalle_contratacion->rfc));
            $phpword->setValue('DOMICILIO',strtoupper($detalle_contratacion->calle));
            $phpword->setValue('FECHAINGRESO',date('d-m-Y',strtotime($detalle_contratacion->fecha_detalle)));
            $phpword->setValue('FECHAANTIGUEDAD',date('d-m-Y',strtotime($detalle_contratacion->fecha_detalle)));
            $phpword->setValue('PUESTO',$detalle_contratacion->puesto);
            $phpword->setValue('DOMICILIOSUCURSAL','');
            $phpword->setValue('SUELDODIARO',$detalle_contratacion->sueldo);
            $sueldo_letras = NumerosEnLetras::convertir(floatval($detalle_contratacion->sueldo), 'pesos', false, 'Centavos');
            $phpword->setValue('SUELDODIARIOLETRAS',strtoupper($sueldo_letras));
            return ["ok" => true, "data" => $phpword->save()];
        }catch(\PhpOffice\PhpWord\Exception\Exception $e){
            return ["ok" => false, "message" => $e->getCode()];
        }
        
    }
}