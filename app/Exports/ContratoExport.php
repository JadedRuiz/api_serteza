<?php

namespace App\Exports;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;
use App\Exports\NumerosEnLetras;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use App\Models\Contrato;

class ContratoExport
{
    public function obtenerContrato($id_contrato, $id_mov)
    {
        $detalle_contratacion = DB::table('rh_detalle_movimiento as dc')
        ->select("ns.representante_legal as represuc","dc.id_detalle",DB::raw('CONCAT(cc.apellido_paterno," ",cc.apellido_materno, " ",cc.nombre) as nombre'), "cc.curp", "cc.rfc", "cd.departamento","cp.puesto","dc.sueldo", "dc.sueldo_neto", "dc.fecha_detalle","dc.observacion","cc.id_candidato","cd.id_departamento","dc.id_puesto","ce.empresa","ce.rfc as rfcempresa","dc.id_nomina","rm.id_status","dc.id_sucursal", "ns.sucursal","ncn.nomina",DB::raw("CONCAT('Calle ',gcd.calle,' #',gcd.numero_exterior, ' ,', gcd.cruzamiento_uno, ' ', gcd.cruzamiento_dos) as calleempresa"),DB::raw("CONCAT('Calle ',gcdd.calle, ' ', gcdd.numero_exterior, ' ', gcdd.cruzamiento_uno, ' Col. ', gcdd.colonia, ' ',gcdd.localidad) as dir_empleado"),DB::raw("CONCAT('Calle', gcd_tres.calle,' #',gcd_tres.numero_exterior, ' ,', gcd_tres.cruzamiento_uno, ' y', gcd_tres.cruzamiento_dos) as callesucursal"),"cp.descripcion as descripcionPuesto","ce.representante_legal","ce.cargo_repre")
        ->join("rh_movimientos as rm","rm.id_movimiento","=","dc.id_movimiento")
        ->join("rh_cat_candidato as cc","cc.id_candidato","=","dc.id_candidato")
        ->join("gen_cat_puesto as cp","cp.id_puesto","=","dc.id_puesto")
        ->join("gen_cat_departamento as cd","cd.id_departamento","=","cp.id_departamento")
        ->join("gen_cat_empresa as ce","ce.id_empresa","=","cd.id_empresa")
        ->join("nom_cat_nomina as ncn","ncn.id_nomina","=","dc.id_nomina")
        ->join("nom_sucursales as ns","ns.id_sucursal","=","dc.id_sucursal")
        ->leftJoin("gen_cat_direccion as gcd","ce.id_direccion","=","gcd.id_direccion")
        ->leftJoin("gen_cat_direccion as gcdd","gcdd.id_direccion","=","cc.id_direccion")
        ->leftJoin("gen_cat_direccion as gcd_tres","ns.id_direccion","=","gcd_tres.id_direccion")
        ->where("dc.id_detalle",$id_mov)
        ->where("dc.activo",1)
        ->first();
        if($detalle_contratacion){
            
            try{
                $file = storage_path('contratos');
                if($id_contrato == 0){
                    $phpword = new TemplateProcessor($file."/CONTRATO GENERICO.docx");
                }else{
                    $info = Contrato::select("url_contrato")
                    ->where("id_contrato",$id_contrato)
                    ->first();
                    if($info){
                        $phpword =  new TemplateProcessor($file."/".$info->url_contrato);
                    }else{
                        $phpword = new TemplateProcessor($file."/CONTRATO GENERICO.docx");
                    }
                }
                $phpword->setValue('REPRESENTANTESUC',$detalle_contratacion->represuc);
                $phpword->setValue('REPRESENTANTE',$detalle_contratacion->representante_legal);
                $phpword->setValue('CARGOREPRE',$detalle_contratacion->cargo_repre);
                $phpword->setValue('EMPRESA',$detalle_contratacion->empresa);
                $phpword->setValue('EMPLEADO', $detalle_contratacion->nombre);
                $phpword->setValue('DOMICILIOEMPRESA',$detalle_contratacion->calleempresa);
                $phpword->setValue('RFCEMPRESA',strtoupper($detalle_contratacion->rfcempresa));
                $phpword->setValue('CURP',strtoupper($detalle_contratacion->curp));
                $phpword->setValue('RFC',strtoupper($detalle_contratacion->rfc));
                $phpword->setValue('DOMICILIO',strtoupper($detalle_contratacion->dir_empleado));
                $phpword->setValue('FECHAINGRESO',date('d-m-Y',strtotime($detalle_contratacion->fecha_detalle)));
                $phpword->setValue('FECHAANTIGUEDAD',date('d-m-Y',strtotime($detalle_contratacion->fecha_detalle)));
                $phpword->setValue('PUESTO',$detalle_contratacion->puesto);
                $phpword->setValue('DESCRIPCIONPUESTO',$detalle_contratacion->descripcionPuesto);
                $phpword->setValue('SUCURSAL',$detalle_contratacion->sucursal);
                $phpword->setValue('DOMICILIOSUCURSAL',$detalle_contratacion->callesucursal);
                $phpword->setValue('SUELDODIARO',$detalle_contratacion->sueldo);
                $sueldo_letras = NumerosEnLetras::convertir(floatval($detalle_contratacion->sueldo), 'pesos', false, 'Centavos');
                $phpword->setValue('SUELDODIARIOLETRAS',strtoupper($sueldo_letras));
                return ["ok" => true, "data" => $phpword->save()];
            }catch(\PhpOffice\PhpWord\Exception\Exception $e){
                return ["ok" => false, "message" => $e->getCode()];
            }
        }
        return $this->crearRespuesta("2","No se ha encontrado la contratación de este empleado, consulte con el administrador","200");
    }
    public function contratoCandidato($id_candidato)
    {
        try{
            $empleado_dato = DB::table('nom_empleados as ne')
            ->select("ns.representante_legal as represuc","gcp.descripcion as descripcionPuesto","ns.sucursal",DB::raw("CONCAT(rcc.nombre, ' ', rcc.apellido_paterno, ' ', rcc.apellido_materno) as nombre"),DB::raw("CONCAT('Calle ',gcd.calle, ' ', gcd.numero_exterior, ' ', gcd.cruzamiento_uno, ' Col. ', gcd.colonia, ' ',gcd.localidad) as dir_empleado"),DB::raw("CONCAT('Calle ',gcdd.calle, ' ', gcdd.numero_exterior, ' ', gcdd.cruzamiento_uno, ' Col. ', gcdd.colonia, ' ',gcdd.localidad) as dir_empresa"),DB::raw("CONCAT(gcdt.calle, ' ', gcdt.numero_exterior, ' ', gcdt.cruzamiento_uno, ' Col. ', gcdt.colonia, ' ',gcdt.localidad) as dir_sucursal"),'gcp.puesto',"gce.empresa","gce.rfc as rfcempresa","gce.representante_legal","rcc.curp","rcc.rfc","ne.fecha_ingreso","ne.fecha_antiguedad","ne.sueldo_diario")
            ->leftJoin("rh_cat_candidato as rcc","rcc.id_candidato","=","ne.id_candidato")
            ->leftJoin("gen_cat_puesto as gcp","gcp.id_puesto","=","ne.id_puesto")
            ->leftJoin("nom_sucursales as ns","ns.id_sucursal","=","ne.id_sucursal")
            ->leftJoin("gen_cat_empresa as gce","gce.id_empresa","=","ns.id_empresa")
            ->leftJoin("gen_cat_direccion as gcd","gcd.id_direccion","=","rcc.id_direccion")
            ->leftJoin("gen_cat_direccion as gcdd","gcdd.id_direccion","=","gce.id_direccion")
            ->leftJoin("gen_cat_direccion as gcdt","gcdt.id_direccion","=","ns.id_direccion")
            ->where("ne.id_candidato",$id_candidato)
            ->first();
            $file = storage_path('contratos');
            $phpword = new TemplateProcessor($file."/CONTRATO GENERICO.docx");
            $phpword->setValue('REPRESENTANTESUC',$empleado_dato->represuc);
            $phpword->setValue('REPRESENTANTE',$empleado_dato->representante_legal);
            $phpword->setValue('EMPRESA',$empleado_dato->empresa);
            $phpword->setValue('EMPLEADO', $empleado_dato->nombre);
            $phpword->setValue('DESCRIPCIONPUESTO',$empleado_dato->descripcionPuesto);
            $phpword->setValue('DOMICILIOEMPRESA',$empleado_dato->dir_empresa);
            $phpword->setValue('RFCEMPRESA',strtoupper($empleado_dato->rfcempresa));
            $phpword->setValue('CURP',strtoupper($empleado_dato->curp));
            $phpword->setValue('RFC',strtoupper($empleado_dato->rfc));
            $phpword->setValue('DOMICILIO',strtoupper($empleado_dato->dir_empleado));
            $phpword->setValue('FECHAINGRESO',date('d-m-Y',strtotime($empleado_dato->fecha_ingreso)));
            $phpword->setValue('FECHAANTIGUEDAD',date('d-m-Y',strtotime($empleado_dato->fecha_antiguedad)));
            $phpword->setValue('PUESTO',$empleado_dato->puesto);
            $phpword->setValue('SUCURSAL',$empleado_dato->sucursal);
            $phpword->setValue('DOMICILIOSUCURSAL',$empleado_dato->dir_sucursal);
            $phpword->setValue('SUELDODIARO',$empleado_dato->sueldo_diario);
            $sueldo_letras = NumerosEnLetras::convertir(floatval($empleado_dato->sueldo_diario), 'pesos', false, 'Centavos');
            $phpword->setValue('SUELDODIARIOLETRAS',strtoupper($sueldo_letras));
            return ["ok" => true, "data" => $phpword->save()];
        }catch(\PhpOffice\PhpWord\Exception\Exception $e){
            return ["ok" => false, "message" => $e->getCode()];
        }
        
    }
    public function obtenerFormatoAlta($id_cliente)
    {
        $cliente = DB::table('gen_cat_cliente as gcc')
        ->select("gcf.nombre as fotografia")
        ->leftJoin("gen_cat_fotografia as gcf","gcf.id_fotografia","=","gcc.id_fotografia")
        ->where("id_cliente",$id_cliente)
        ->first();

        if($cliente){
            $img_logo = storage_path('cliente')."/".$cliente->fotografia;

        }else{
            return ["ok" => false, "message" => "Ha ocurrido un error al generar el formato"];
        }
        // return ["ok" => true, "data" => $img_logo];
        //PINTAR EXCEL
        try{
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getProperties()
            ->setCreator("Serteza")
            ->setLastModifiedBy("Serteza")
            ->setTitle("ALTA EMPLEADOS")
            ->setSubject("Jaded Enrique Ruiz Pech")
            ->setDescription("Documento generado por Serteza")
            ->setKeywords("Serteza")
            ->setCategory("Recursos humanos");
            $i=1;
            $spreadsheet->getActiveSheet()->mergeCells("A1:C2");

            $drawing = new Drawing();
            $drawing->setName('Cliente');
            $drawing->setDescription('Cliente logo');
            $drawing->setPath($img_logo);
            $drawing->setHeight(50);
            $drawing->setCoordinates('A1');
            $drawing->setOffsetX(10);
            $drawing->setWorksheet($spreadsheet->getActiveSheet());

            $objRichText = new RichText();
            $objBold = $objRichText->createTextRun('FORMATO ALTA DE TRABAJADORES');
            $objBold->getFont()->setBold(true)
            ->setSize("18");
            $spreadsheet->getActiveSheet()->getCell('D1')->setValue($objRichText);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('D2', "SISTEMA DE RECURSOS HUMANOS");
            $i++;
            $i++;
            foreach(range('A','Z') as $columnID) {
                $spreadsheet->getActiveSheet()->getColumnDimension($columnID)
                    ->setAutoSize(true);
            }
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('A'.$i, "Empresa");
            $spreadsheet->getActiveSheet()->getStyle('A3:Z3')
            ->getFill()->setFillType(Fill::FILL_SOLID);
            $spreadsheet->getActiveSheet()->getStyle('A3:Z3')
            ->getFill()->getStartColor()->setRGB('11CBEF');
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('B'.$i, "RFC_Empresa");
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('C'.$i, "Sucursal");
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('D'.$i, "Apellido Paterno");
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('E'.$i, "Apellido Materno");
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('F'.$i, "Nombre");
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('G'.$i, "Rfc");
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('H'.$i, "Curp");
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('I'.$i, "Imss");
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('J'.$i, "Fecha nacimiento");
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('K'.$i, "Calle");
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('L'.$i, "Num. Interior");
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('M'.$i, "Num. exterior");
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('N'.$i, "Cruzamientos");
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('O'.$i, "Colonia");
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('P'.$i, "Municipio");
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('Q'.$i, "Estado");
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('R'.$i, "C.P");
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('S'.$i, "Telefono");
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('T'.$i, "Departamento");
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('U'.$i, "Puesto");
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('V'.$i, "Sueldo");
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('W'.$i, "Sueldo integrado");
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('X'.$i, "Fecha ingreso");
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('Y'.$i, "Fecha antiguedad");
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('Z'.$i, "Tipo nomina");

            $writer = new Xlsx($spreadsheet);
            $writer->save(storage_path('excel')."/temp_excel.xlsx");
            $content = base64_encode(file_get_contents(storage_path('excel')."/temp_excel.xlsx"));
            return ["ok" => true, "data" => $content];
        }catch (\Throwable $th) {
            return ["ok"=> false, "message" => "Ha ocurrido un error: ".$th->getMessage()];
        }
        
    }
}