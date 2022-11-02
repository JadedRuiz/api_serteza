<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade as PDF;
use App\Exports\ReporteExport;
use Illuminate\Support\Facades\Storage;
use App\Exports\FacturaExport;

class ReporteController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function reporteContratado($id_detalle)
    {
        $detalle_contratacion = DB::table('rh_detalle_movimiento as dc')
        ->select(DB::raw('CONCAT(cc.nombre, " ",cc.apellido_paterno, " ", cc.apellido_materno) as nombre'), "cd.departamento","cp.puesto","dc.sueldo","dc.sueldo_neto","dc.fecha_detalle","dc.observacion","cd.id_departamento","ce.empresa","ccd.cliente","cu.nombre as usuario","cc.rfc","cc.curp","cc.numero_seguro","cc.correo","cdd.calle","cdd.numero_interior","cdd.numero_exterior","cdd.cruzamiento_uno","cdd.cruzamiento_dos","cdd.colonia","cdd.municipio","cdd.estado","cf.nombre as fotografia","cff.nombre as name_foto","cc.telefono","cc.telefono_dos", "cc.fecha_nacimiento","cc.edad","ns.sucursal","ncn.nomina")
        ->join("rh_movimientos as mc","mc.id_movimiento","=","dc.id_movimiento")
        ->join("gen_cat_puesto as cp","cp.id_puesto","=","dc.id_puesto")
        ->join("gen_cat_departamento as cd","cd.id_departamento","=","cp.id_departamento")
        ->join("gen_cat_empresa as ce","ce.id_empresa","=","cd.id_empresa")
        ->join("rh_cat_candidato as cc","cc.id_candidato","=","dc.id_candidato")
        ->leftJoin("gen_cat_cliente as ccd","ccd.id_cliente","=","cc.id_cliente")
        ->leftJoin("gen_cat_fotografia as cff","cff.id_fotografia","=","ccd.id_fotografia")
        ->leftJoin("gen_cat_direccion as cdd","cdd.id_direccion","=","cc.id_direccion")
        ->leftJoin("gen_cat_fotografia as cf","cf.id_fotografia","=","cc.id_fotografia")
        ->leftJoin("gen_cat_usuario as cu","cu.id_usuario","=","mc.usuario_creacion")
        ->leftJoin("nom_sucursales as ns","ns.id_sucursal","=","dc.id_sucursal")
        ->leftJoin("nom_cat_nomina as ncn","ncn.id_nomina","=","dc.id_nomina")
        ->where("dc.id_detalle",$id_detalle)
        ->where("mc.activo",1)
        ->get();
        $export = new ReporteExport();
        try{
            return $this->crearRespuesta(1,$export->generarReporte($detalle_contratacion,"AltaReport"),200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
        // $pdf = PDF::loadView("reporte_contratado",compact('detalle_contratacion'))
        // ->setPaper('A4',);
        // return $pdf->stream();
    }
    public function reporteModificacion($id_detalle)
    {
        $detalle_modificacion = DB::table('rh_detalle_movimiento as dc')
        ->select("cc.id_candidato","dc.id_detalle",DB::raw('CONCAT(cc.nombre, " ",cc.apellido_paterno, " ", cc.apellido_materno) as nombre'), "cd.departamento","cp.puesto","dc.sueldo","dc.sueldo_neto","dc.fecha_detalle","dc.observacion","cd.id_departamento","ce.empresa","ccd.cliente","cu.nombre as usuario","cc.rfc","cc.curp","cc.numero_seguro","cc.correo","cdd.calle","cdd.numero_interior","cdd.numero_exterior","cdd.cruzamiento_uno","cdd.cruzamiento_dos","cdd.colonia","cdd.municipio","cdd.estado","cf.nombre as fotografia","cff.nombre as name_foto","cc.telefono","cc.telefono_dos", "cc.fecha_nacimiento","cc.edad","ns.sucursal","dc.id_status","ncn.nomina")
        ->join("rh_movimientos as mc","mc.id_movimiento","=","dc.id_movimiento")
        ->join("gen_cat_puesto as cp","cp.id_puesto","=","dc.id_puesto")
        ->join("gen_cat_departamento as cd","cd.id_departamento","=","cp.id_departamento")
        ->join("gen_cat_empresa as ce","ce.id_empresa","=","cd.id_empresa")
        ->join("rh_cat_candidato as cc","cc.id_candidato","=","dc.id_candidato")
        ->leftJoin("gen_cat_cliente as ccd","ccd.id_cliente","=","cc.id_cliente")
        ->leftJoin("gen_cat_fotografia as cff","cff.id_fotografia","=","ccd.id_fotografia")
        ->leftJoin("gen_cat_direccion as cdd","cdd.id_direccion","=","cc.id_direccion")
        ->leftJoin("gen_cat_fotografia as cf","cf.id_fotografia","=","cc.id_fotografia")
        ->leftJoin("gen_cat_usuario as cu","cu.id_usuario","=","mc.usuario_creacion")
        ->leftJoin("nom_sucursales as ns","ns.id_sucursal","=","dc.id_sucursal")
        ->leftJoin("nom_cat_nomina as ncn","ncn.id_nomina","=","dc.id_nomina")
        ->where("dc.id_detalle",$id_detalle)
        ->where("mc.activo",1)
        ->first();
        if($detalle_modificacion->id_status == "1"){
            //Si la modificación ya fue realizada, se recupera la ultima para ver los cambios
            $detalle_contratacion = DB::table('rh_detalle_movimiento as dc')
            ->select("cd.departamento","cp.puesto","dc.sueldo as sueldo_diario","dc.sueldo_neto as sueldo_integrado","dc.fecha_detalle as fecha_ingreso","dc.observacion as descripcion","cd.id_departamento","ce.empresa","ns.sucursal","ncn.nomina")
            ->join("rh_movimientos as rm","rm.id_movimiento","=","dc.id_movimiento")
            ->join("gen_cat_puesto as cp","cp.id_puesto","=","dc.id_puesto")
            ->join("gen_cat_departamento as cd","cd.id_departamento","=","cp.id_departamento")
            ->join("gen_cat_empresa as ce","ce.id_empresa","=","cd.id_empresa")
            ->leftJoin("nom_cat_nomina as ncn","ncn.id_nomina","=","dc.id_nomina")
            ->leftJoin("nom_sucursales as ns","ns.id_sucursal","=","dc.id_sucursal")
            ->where("dc.id_candidato",$detalle_modificacion->id_candidato)
            ->where("dc.id_detalle","<>",$detalle_modificacion->id_detalle)
            ->whereIn("rm.tipo_movimiento",["A","M"])
            ->where("dc.activo",1)
            ->orderBy("rm.fecha_movimiento","DESC")
            ->first();
        }else{
            //La modificación aun no ha sido aplicada
            $detalle_contratacion = DB::table('nom_empleados as ne')
            ->select("cd.departamento","cp.puesto","ne.sueldo_diario","ne.sueldo_integrado","ne.fecha_ingreso","ne.descripcion","cd.id_departamento","ce.empresa","ns.sucursal")
            ->join("gen_cat_puesto as cp","cp.id_puesto","=","ne.id_puesto")
            ->join("gen_cat_departamento as cd","cd.id_departamento","=","cp.id_departamento")
            ->join("gen_cat_empresa as ce","ce.id_empresa","=","cd.id_empresa")
            ->leftJoin("nom_sucursales as ns","ns.id_sucursal","=","ne.id_sucursal")
            ->where("id_candidato",$detalle_modificacion->id_candidato)
            ->first();
        }
        $datos = [
            "detalle_contratacion" => $detalle_contratacion,
            "detalle_modificacion" => $detalle_modificacion
        ];
        $export = new ReporteExport();
        try{
            return $this->crearRespuesta(1,$export->generarReporte($datos,"ModificacionReport"),200);
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
    }
    public function reporteContrato($id_movimiento)
    {
        $reporte_contrato = DB::table('rh_movimientos as mc')
        ->select("cc.cliente","cf.nombre as foto_cliente","mc.fecha_movimiento","mc.id_movimiento as folio","cu.nombre as usuario","cu.id_usuario as detalle","mc.fecha_movimiento as fecha_hoy","mc.tipo_movimiento")
        ->join("gen_cat_cliente as cc","cc.id_cliente","=","mc.id_cliente")
        ->join("gen_cat_fotografia as cf","cf.id_fotografia","=","cc.id_fotografia")
        ->join("gen_cat_usuario as cu","cu.id_usuario","=","mc.usuario_creacion")
        ->where("mc.id_movimiento",$id_movimiento)
        ->get();
        if(count($reporte_contrato)>0){
            $reporte_contrato[0]->detalle = [];
            $reporte_contrato[0]->fecha_movimiento = date('d-m-Y',strtotime($reporte_contrato[0]->fecha_movimiento));
            $reporte_contrato[0]->fecha_hoy = date('d-m-Y');
            $detalle = DB::table('rh_detalle_movimiento as dc')
            ->select("cc.id_candidato","cc.nombre","cc.apellido_paterno","cc.apellido_materno","ce.empresa","dp.departamento","cp.puesto","dc.sueldo", "dc.sueldo_neto","dc.observacion","dc.fecha_detalle","ns.sucursal","ncn.nomina")
            ->join("rh_cat_candidato as cc","cc.id_candidato","=","dc.id_candidato")
            ->join("gen_cat_puesto as cp","cp.id_puesto","=","dc.id_puesto")
            ->join("gen_cat_departamento as dp","dp.id_departamento","=","cp.id_departamento")
            ->join("gen_cat_empresa as ce","ce.id_empresa","=","dp.id_empresa")
            ->join("nom_sucursales as ns","ns.id_sucursal","=","dc.id_sucursal")
            ->leftJoin("nom_cat_nomina as ncn","ncn.id_nomina","=","dc.id_nomina")
            ->where("dc.id_movimiento",$id_movimiento)
            ->where("dc.activo",1)
            ->get();
            if(count($detalle)>0){
                $reporte_contrato[0]->detalle = $detalle;
                $export = new ReporteExport();
                try{
                    return $this->crearRespuesta(1,$export->generarReporte($reporte_contrato,"GeneralReport"),200);
                }catch(Throwable $e){
                    return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
                }
            }
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
    public function reporteDepartamento($id_empresa,$id_cliente)
    {
        $info_cliente = DB::table('gen_cat_cliente as gcc')
        ->select("gcf.nombre as name_foto","cliente")
        ->leftJoin("gen_cat_fotografia as gcf","gcf.id_fotografia","=","gcc.id_fotografia")
        ->where("id_cliente",$id_cliente)
        ->first();
        $datos = DB::table('gen_cat_departamento as gcd')
        ->select("gcd.id_departamento","gcd.departamento","gcd.departamento as puestos","gcd.departamento as name_foto","gcd.departamento as cliente","gce.empresa","gce.empresa as vacantes")
        ->leftJoin("gen_cat_empresa as gce","gce.id_empresa","=","gcd.id_empresa")
        ->where("gcd.id_empresa",$id_empresa)
        ->get();
        if(count($datos)>0){
            foreach($datos as $dato){
                $puestos = DB::table('gen_cat_puesto')
                ->select("id_puesto","id_puesto as contratados","puesto","autorizados","descripcion")
                ->where("id_departamento",$dato->id_departamento)
                ->get();
                if(count($puestos)>0){
                    $vacantes = 0;
                    foreach($puestos as $puesto){
                        $puesto->contratados = $this->obtenerContratados($puesto->id_puesto);
                        $vacantes += ($puesto->autorizados - $puesto->contratados);
                    }
                    $dato->vacantes = $vacantes;
                    $dato->cliente = $info_cliente->cliente;
                    $dato->name_foto = $info_cliente->name_foto;
                    $dato->puestos = $puestos;
                }else{
                    $dato->puesto = [];
                }
            }
            $export = new ReporteExport();
            try{
                return $this->crearRespuesta(1,$export->generarReporte($datos,"DepartamentoReport"),200);
            }catch(Throwable $e){
                return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
            }
        }
        return $this->crearRespuesta(2,"La empresa no cuenta con despartamentos",301);
    }
    public function generarFactura($id_factura,$tipo,$tipo_envio)
    {
        if($tipo == 1){     //PDF
            $reporte = new FacturaExport();
            switch($tipo_envio){
                case 1 :    //BASE64
                    return $reporte->generarFactura($id_factura);
                    break;
                case 2 :    //Descarga
                    //$path = 'temp_file_pdf.pdf';
                    $contents = base64_decode($reporte->generarFactura($id_factura)["data"]);
                    //file_put_contents($path, $contents);
                    return response($contents)
                	->header('Content-Type','application/pdf')
                	->header('Pragma','public')
                	->header('Content-Disposition','inline; filename="qrcodeimg.pdf"')
                	->header('Cache-Control','max-age=60, must-revalidate');
                    break;
                default :
                    break;
            }
        }
        if($tipo == 2){     //XML
            $xml = base64_encode(DB::table("fac_factura")->select("xml")->where("id_factura",$id_factura)->first()->xml);
            switch($tipo_envio){
                case 1 :    //BASE64
                    return $this->crearRespuesta(1,$xml,"200");
                    break;
                case 2 :    //Descarga
                    $path = 'temp_file_xml.xml';
                    $contents = base64_decode($xml);
                    file_put_contents($path, $contents);
                    $headers = array(
                      'Content-Type: application/octet-stream',
                      'Content-Disposition: attachment; filename=factura.xml'
                    );
                    return response()->download($path, 'factura.xml',$headers)->deleteFileAfterSend(true);
                    break;
                default :
                    break;
            }
        }
        if($tipo == 3){     //Ambos

        }
        if($tipo == 4){     //Información

        }
    }
}
