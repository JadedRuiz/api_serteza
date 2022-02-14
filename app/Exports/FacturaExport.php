<?php 

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Codedge\Fpdf\Fpdf\Fpdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Storage;

require_once(storage_path("lib")."/java/Java.inc.php");

class FacturaExport {

    public function generarReporteFactura($datos)
    {
        $id_cliente = DB::table('gen_cat_empresa as gce')
        ->select("gce.empresa","gcf.nombre as foto")
        ->join("gen_cat_fotografia as gcf","gcf.id_fotografia","=","gce.id_fotografia")
        ->where("id_empresa",$datos["id_empresa"])
        ->first();
        if(!isset($datos["tipo"])){
            $xml = simplexml_load_file($datos["xml"]);
        }else{
            $xml = simplexml_load_string($datos["xml"]);
        }
        $namespaces = $xml->getNamespaces(true);
        $xml->registerXPathNamespace('c', $namespaces['cfdi']);
        $xml->registerXPathNamespace('t', $namespaces['tfd']);
        $xml->registerXPathNamespace('n', $namespaces['nomina12']);
        $sello = "";
        $no_csd = "";
        $fecha = "";
        foreach($xml->xpath('//c:Comprobante') as $dato){
            $no_csd = $dato["NoCertificado"];
            $fecha = date('d-m-Y',strtotime($dato["Fecha"]));
        }
        $rfc_emisor="";
        $nombre_emisor = "";

        foreach($xml->xpath('//c:Emisor') as $dato){
            $rfc_emisor = $dato["Rfc"];
            $nombre_emisor = $dato["Nombre"];
        }
        $nombre_empleado = "";
        $rfc_empleado = "";
        foreach($xml->xpath('//c:Receptor') as $dato){
            $rfc_empleado = $dato["Rfc"];
            $nombre_empleado = $dato["Nombre"];
        }
        $fechatimbre = "";
        $uuid = "";
        $cerSAT = "";
        $selloSAT = "";
        foreach($xml->xpath('//t:TimbreFiscalDigital') as $dato){
            $fechatimbre = date('d-m-Y H:i:s',strtotime($dato["FechaTimbrado"]));
            $sello = $dato["SelloCFD"];
            $uuid = $dato["UUID"];
            $cerSAT = $dato["NoCertificadoSAT"];
            $selloSAT = $dato["SelloSAT"];
        }
        $fecha_pago = "";
        $fecha_pago_i = "";
        $fecha_pago_f = "";
        foreach($xml->xpath('//n:Nomina') as $dato){
            $fecha_pago = $dato["FechaPago"];
            $fecha_pago_i = $dato["FechaInicialPago"];
            $fecha_pago_f = $dato["FechaFinalPago"];
            // $cerSAT = $dato["NoCertificadoSAT"];
        }
        $registro_patronal = "";
        foreach($xml->xpath('//n:Emisor') as $dato){
            $registro_patronal = $dato["RegistroPatronal"];
        }
        $sueldo = "";
        $puesto = "";
        $tipo_jornada = "";
        $periocidad = "";
        $curp = "";
        $num_ss = "";
        $sueldo_integrado = "";
        $tipo_contrato = "";
        $inicio_relacion = "";
        $tipo_regimen = "";
        foreach($xml->xpath('//n:Receptor') as $dato){
            $sueldo = floatval($dato["SalarioBaseCotApor"]."");
            $puesto = $dato["Puesto"];
            $tipo_jornada = $dato["TipoJornada"];
            $periocidad = $dato["PeriodicidadPago"];
            $curp = $dato["Curp"];
            $num_ss = $dato["NumSeguridadSocial"];
            $sueldo_integrado = floatval($dato["SalarioDiarioIntegrado"]."");
            $tipo_contrato = $dato["TipoContrato"];
            $inicio_relacion = date('d-m-Y',strtotime($dato["FechaInicioRelLaboral"].""));
            $tipo_regimen = $dato["TipoRegimen"];
        }
        //Genera PDF
        $cont=0;
        $pdf = new Fpdf('P','mm','A4');
        $pdf->AddPage();

        $pdf->SetFont('Arial','B',12);
        $pdf->SetFillColor(213, 216, 220);
        $pdf->SetDrawColor(213, 216, 220);
        if($id_cliente->foto != ""){
            if(file_exists(Storage::disk('empresa')->path($id_cliente->foto))){
                $extension = strtoupper(explode(".",$id_cliente->foto)[1]);
                $pdf->Image(Storage::disk('empresa')->path($id_cliente->foto),13,14,40,25,$extension,'');
            }
        }
        $pdf->Cell(110,5,"Recibo de nomina",0,0,"L");
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(80,5,"Recibo de nomina",1,0,"C",true);
        $pdf->Ln(); 
        $pdf->SetFont('Arial','',7);
        $pdf->Cell(110,5,"",0,0,"C");
        $pdf->Cell(25,5,"Folio Fiscal",1,0,"L");
        $pdf->Cell(55,5,$uuid,1,0,"L");
        $pdf->Ln(); 
        $pdf->Cell(110,5,"",0,0,"C");
        $pdf->Cell(25,5,"Fecha",1,0,"L");
        $pdf->Cell(55,5,$fecha,1,0,"L");
        $pdf->Ln(); 
        $pdf->Cell(110,5,"",0,0,"C");
        $pdf->Cell(25,5,"Cert CSD",1,0,"L");
        $pdf->Cell(55,5,$no_csd,1,0,"L");
        $pdf->Ln(); 
        $cont=$cont+20;

        $pdf->Ln();
        $pdf->Ln();
        //$pdf->SetXY(310,10);          // Primero establece Donde estará la esquina superior izquierda donde estará tu celda 
        //$pdf->SetTextColor(255,255,255);  // Establece el color del texto (en este caso es blanco) 
        // establece el color del fondo de la celda (en este caso es AZUL 

        $pdf->SetFont('Arial','',8);
        $pdf->Cell(105,5,"Datos del Receptor",1,0,"C",true);
        $pdf->Cell(5,5,"",0,0,"L");
        $pdf->Cell(80,5,"Datos del Emisor",1,0,"C",true);
        $pdf->Ln();
        $pdf->Cell(20,5,"RFC",1,0,"L");
        $pdf->Cell(85,5,$rfc_empleado,1,0,"L");
        $pdf->Cell(5,5,"",0,0,"L");
        $pdf->Cell(80,5,$nombre_emisor,0,0,"C");
        $pdf->Ln();
        $pdf->Cell(20,5,"Nombre",1,0,"L");
        $pdf->Cell(85,5,utf8_decode($nombre_empleado),1,0,"L");
        $pdf->Cell(5,5,"",0,0,"L");
        $pdf->Cell(80,5,$rfc_emisor,0,0,"C");
        $pdf->Ln();
        $pdf->Cell(20,5,utf8_decode("Número de S.S"),1,0,"L");
        $pdf->Cell(85,5,$num_ss,1,0,"L");
        $pdf->Cell(5,5,"",0,0,"C");
        $pdf->Cell(85,5,'',0,0,"C");
        $pdf->Ln();
        $pdf->Cell(20,5,"CURP",1,0,"L");
        $pdf->Cell(85,5,$curp,1,0,"L");
        $pdf->Cell(5,5,"",0,0,"L");
        $pdf->Cell(80,5,"Registro Patronal: ".$registro_patronal,0,0,"C");
        $pdf->Ln();
        // $puesto="";
        // if($datos[30]=="X"){$puesto="";}else{$puesto=$datos[30];}
        $pdf->Cell(20,5,utf8_decode("Puesto"),1,0,"L");
        $pdf->Cell(85,5,$puesto,1,0,"L");
        $pdf->Cell(5,5,"",0,0,"L");
        $pdf->Cell(85,5,"Tipo Regimen: ".$tipo_regimen,0,0,"C");
        $pdf->Ln();
        // $departamento="";
        // if($datos[30]=="X"){$cuenta="";}else{$cuenta=$datos[30];}
        $pdf->Cell(20,5,utf8_decode("Fecha pago"),1,0,"L");
        $pdf->Cell(25,5,date('d-m-Y',strtotime($fecha_pago)),1,0,"L");
        $pdf->Cell(10,5,"Del",1,0,"L");
        $pdf->Cell(20,5,date('d-m-Y',strtotime($fecha_pago_i)),1,0,"L");
        $pdf->Cell(10,5,"Al",1,0,"L");
        $pdf->Cell(20,5,date('d-m-Y',strtotime($fecha_pago_f)),1,0,"L");
        $pdf->Cell(5,5,"",0,0,"L");
        $pdf->Cell(85,5,"Clave de Riesgo: ".'',0,0,"C");
        $pdf->Ln();

        $pdf->Cell(20,5,utf8_decode("Periodicidad:"),1,0,"L");
        $pdf->Cell(25,5,$periocidad,1,0,"L");
        $pdf->Cell(40,5,utf8_decode("Inicio Relación Laboral"),1,0,"L");
        $pdf->Cell(20,5,$inicio_relacion,1,0,"L");
        $pdf->Cell(5,5,"",0,0,"L");
        $pdf->Cell(80,5,"",0,0,"C",true);
        $pdf->Ln();
        $pdf->Cell(20,5,utf8_decode("Sueldo Base:"),1,0,"L");
        $pdf->Cell(25,5,"$ ".number_format($sueldo,2,".",","),1,0,"L");
        $pdf->Cell(40,5,utf8_decode("Sueldo integrado"),1,0,"L");
        $pdf->Cell(20,5,"$ ".number_format($sueldo_integrado,2,".",","),1,0,"L");
        $pdf->Cell(5,5,"",0,0,"L");
        $pdf->Cell(80,5,"",0,0,"C");
        $pdf->Ln();
        $pdf->Cell(20,5,utf8_decode("Tipo Jornada:"),1,0,"L");
        $pdf->Cell(25,5,$tipo_jornada,1,0,"L");
        $pdf->Cell(40,5,utf8_decode("Tipo Contratacion"),1,0,"L");
        $pdf->Cell(20,5,$tipo_contrato,1,0,"L");
        $pdf->Cell(5,5,"",0,0,"L");
        $pdf->Cell(80,5,"",0,0,"C");
        $pdf->Ln();

        $pdf->Cell(105,5,"",1,0,"L",true);
        $pdf->Cell(5,5,"",0,0,"L");
        $pdf->Cell(80,5,"",0,0,"C");
        $pdf->Ln();
        $cont=$cont+60;
        $pdf->Ln();



        $pdf->Cell(90,5,"Percepciones",0,0,"C",true);
        $pdf->Cell(10,5,"",0,0,"C");
        $pdf->Cell(90,5,"Deducciones",0,0,"C",true);
        $pdf->Ln(3);

        //Inicia
        $tamaño_per = count($xml->xpath('//n:Percepciones/n:Percepcion'))-1;
        $tamaño_de = count($xml->xpath('//n:Deducciones/n:Deduccion'))-1;
        $no_ite = 0;
        $suma_per = 0;
        $suma_de = 0;
        if($tamaño_per > $tamaño_de){
            $no_ite = $tamaño_per;
        }else{
            $no_ite = $tamaño_de;
        }
        for($i=0;$i<=$no_ite;$i++){
            $pdf->Ln(3);
            if($tamaño_per >= $i){  
                $pdf->Cell(70,5,$xml->xpath('//n:Percepciones/n:Percepcion')[$i]["Concepto"],0,0,"L");
                if($xml->xpath('//n:Percepciones/n:Percepcion')[($i)]["ImporteExento"] != "0.00"){
                    $pdf->Cell(20,5,'$'.number_format(floatval($xml->xpath('//n:Percepciones/n:Percepcion')[($i)]["ImporteExento"].""),2,".",","),0,0,"R");
                    $suma_per += floatval($xml->xpath('//n:Percepciones/n:Percepcion')[($i)]["ImporteExento"]."");
                }else{
                    $pdf->Cell(20,5,'$'.number_format(floatval($xml->xpath('//n:Percepciones/n:Percepcion')[($i)]["ImporteGravado"].""),2,".",","),0,0,"R");
                    $suma_per += floatval($xml->xpath('//n:Percepciones/n:Percepcion')[($i)]["ImporteGravado"]."");
                }
            }else{
                $pdf->Cell(70,5,"",0,0,"L");
                $pdf->Cell(20,5,"",0,0,"R");
            }
            $pdf->Cell(10,5,"",0,0,"C");
            if($tamaño_de >= $i){
                $pdf->Cell(70,5,$xml->xpath('//n:Deducciones/n:Deduccion')[$i]["Concepto"],0,0,"L");
                $pdf->Cell(20,5,'$'.number_format(floatval($xml->xpath('//n:Deducciones/n:Deduccion')[$i]["Importe"].""),2,".",","),0,0,"R");
                $suma_de += floatval($xml->xpath('//n:Deducciones/n:Deduccion')[$i]["Importe"]."");
            }
        }
        $pdf->Ln(2);
        if(count($xml->xpath('//n:OtrosPagos/n:OtroPago'))>0){
            
            $pdf->Ln(4);
            $pdf->Cell(55);
            $pdf->Cell(90,5,"OTROS PAGOS",1,0,"C",true);
            foreach($xml->xpath('//n:OtrosPagos/n:OtroPago') as $dato){
                $pdf->Ln();
                $pdf->Cell(55);
                $pdf->Cell(70,5,$dato["Concepto"],0,0,"L");
                $pdf->Cell(20,5,'$'. number_format(floatval($dato["Importe"].""),2,".",","),0,0,"R");
            }
            $pdf->Ln(4);
        }
        $pdf->Ln(2);
        $pdf->Cell(65,5,"PERCEPCIONES",1,0,"C",true);
        $pdf->Cell(65,5,"DEDUCCIONES",1,0,"C",true);
        $pdf->Cell(60,5,"TOTAL NETO",1,0,"C",true);
        $pdf->Ln();
        $pdf->Cell(65,5,number_format($suma_per,2,".",","),1,0,"C");
        $pdf->Cell(65,5,number_format($suma_de,2,".",","),1,0,"C");
        $cuenta="";
        // if($datos[27]=="X"){$cuenta="";}else{$cuenta=$datos[27];}
        $pdf->Cell(60,5,number_format(($suma_per - $suma_de),2,".",","),1,0,"C");
        $pdf->Ln();
        $pdf->Cell(190,1,"",1,0,"C",true);

        $pdf->Ln();
        $pdf->Ln();
        $cont=$cont+8;
        // if($datos[59]!="X"){
        //     $pdf->Cell(190,5,utf8_decode("Pago de indemnización"),1,0,"C",true);
        //     $pdf->Ln();
        //     $pdf->Cell(38,5,utf8_decode("Último sueldo del mes"),1,0,"C",true);
        //     $pdf->Cell(38,5,utf8_decode("Años de servicio"),1,0,"C",true);
        //     $pdf->Cell(38,5,"Ingreso no acumulable",1,0,"C",true);
        //     $pdf->Cell(38,5,"Ingreso acumulable",1,0,"C",true);
        //     $pdf->Cell(38,5,"Total pagado",1,0,"C",true);
        //     $pdf->Ln();

        //     $pdf->Cell(38,5,"$ ".formatonum($datos[76]),1,0,"C");
        //     $pdf->Cell(38,5,$datos[75],1,0,"C");
        //     $pdf->Cell(38,5,"$ ".(formatonum($datos[78])==""? "0.00" : formatonum($datos[78])),1,0,"C");
        //     $pdf->Cell(38,5,"$ ".formatonum($datos[77]),1,0,"C");
        //     $pdf->Cell(38,5,"$ ".formatonum($datos[74]),1,0,"C");
        //     $pdf->Ln();
        //     $cont=$cont+15;
        //     $pdf->Cell(190,1,"",1,0,"C",true);
        // }


        $pdf->Ln();

        $pdf->MultiCell(190,5,utf8_decode("RECIBI DE ".utf8_decode($nombre_emisor)." LA CANTIDAD QUE APARECE EN LA COLUMNA DE NETO A PAGAR,CORRESPONDIENTES AL PERÍODO QUE HOY TERMINA SIN QUE A LA FECHA SE ME ADEUDE CANTIDAD ALGUNA POR OTRO CONCEPTO, HABIÉNDOSEME HECHO LOS DESCUENTOS DE LEY COMO LOS DE CARÁCTER PRIVADO"));
        $cont=$cont+15;

        $pdf->Ln();


        $pdf->Cell(70,5,utf8_decode("Fecha y hora de Expedición"),0,0,"L");
        $pdf->Cell(70,5,$fecha,0,0,"L");
        $pdf->Ln();
        $pdf->Cell(70,5,utf8_decode("Número de Serie de Certificado del Emisor"),0,0,"L");
        $pdf->Cell(70,5,$no_csd,0,0,"L");
        $pdf->Ln();
        $pdf->Cell(70,5,utf8_decode("Número de Serie de Certificado del SAT"),0,0,"L");
        $pdf->Cell(70,5,$cerSAT,0,0,"L");
        $pdf->Ln();
        $pdf->Cell(70,5,utf8_decode("Folio Fiscal"),0,0,"L");
        $pdf->Cell(70,5,$uuid,0,0,"L");
        $pdf->Ln();
        $pdf->Cell(70,5,utf8_decode("Fecha y hora de Certificación"),0,0,"L");
        $pdf->Cell(70,5,$fechatimbre,0,0,"L");
        $pdf->Ln();
        $pdf->Ln();
        $pdf->SetFont('Arial','B',6);
        $pdf->Cell(180,5,utf8_decode("Cadena Original del Complemento de Certificación Digital del SAT"),0,0,"L");
        $pdf->SetFont('Arial','',6);
        $pdf->Ln();
        $pdf->MultiCell(190,3,utf8_decode("||1.0|".$uuid."|".$fechatimbre."|".$sello."|".$no_csd."||"));

        $pdf->SetFont('Arial','B',6);
        $pdf->Cell(180,5,utf8_decode("Sello Digital del Emisor"),0,0,"L");

        $pdf->SetFont('Arial','',6);
        $pdf->Ln();
        $pdf->MultiCell(190,3,utf8_decode($sello));

        $pdf->SetFont('Arial','B',6);
        $pdf->Cell(180,5,utf8_decode("Sello Digital SAT"),0,0,"L");

        $pdf->SetFont('Arial','',6);
        $pdf->Ln();
        $pdf->MultiCell(190,3,utf8_decode($selloSAT));
        // if(isset($datos["codigo_qr"])){
        //     $pdf->Ln(2);
        //     $h_img = fopen($datos["codigo_qr"], "rb");
        //     $img = fread($h_img, filesize($datos["codigo_qr"]));
        //     fclose($h_img);
        //     $pic = 'data:image/jpg;base64,' . base64_encode($img);
        //     $pdf->Image($pic,80,5,35,35,'JPG');
        // }
        return [
            "ok" => true,
            "pdf" => base64_encode($pdf->Output("S","ReciboFactura.pdf"))
        ];
    }
    public function generarExcelReporte($datos_vista)
    {
        //PINTAR EXCEL
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
        ->setCreator("Serteza")
        ->setLastModifiedBy("Serteza")
        ->setTitle("Facturas")
        ->setSubject("Jaded Enrique Ruiz Pech")
        ->setDescription("Documento generado por Serteza")
        ->setKeywords("Serteza")
        ->setCategory("Nomina");
        $i=1;
        $objRichText = new RichText();
        $objBold = $objRichText->createTextRun('SISTEMA DE TIMBRADO DE NÓMINA');
        $objBold->getFont()->setBold(true);

        $spreadsheet->getActiveSheet()->getCell('E1')->setValue($objRichText);
        $i++;
        foreach(range('A','G') as $columnID) {
            $spreadsheet->getActiveSheet()->getColumnDimension($columnID)
                ->setAutoSize(true);
        }
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('A'.$i, "Id timbrado");
        $spreadsheet->getActiveSheet()->getStyle('A2:G2')
        ->getFill()->setFillType(Fill::FILL_SOLID);
        $spreadsheet->getActiveSheet()->getStyle('A2:G2')
        ->getFill()->getStartColor()->setRGB('A8DF16');
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('B'.$i, "Nombre");
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('C'.$i, "RFC");
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('D'.$i, "Codigo empleado");
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('E'.$i, "UUID");
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('F'.$i, "Fecha pago");
        $spreadsheet->setActiveSheetIndex(0)->setCellValue('G'.$i, "Fecha timbrado");
        $i++;
        foreach($datos_vista as $datos){
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('A'.$i, $datos->id_timbrado);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('B'.$i, $datos->nombre);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('C'.$i, $datos->rfc);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('D'.$i, $datos->codigo_empleado);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('E'.$i, $datos->uuid);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('F'.$i, $datos->fecha_pago);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('G'.$i, $datos->fecha_timbrado);
            $i++;
        }
        $writer = new Xlsx($spreadsheet);
        $writer->save(storage_path('excel')."/temp_excel.xlsx");
        $content = base64_encode(file_get_contents(storage_path('excel')."/temp_excel.xlsx"));
        return $content;
    }
    public function generarTimbrado($datos)
    {
        $fachada = new \Java("mx.emcor.uslibphp.bridges.cfdi33.FachadaCFD33");
        $fachada->crearFachada();
        return "entro";
    }
}