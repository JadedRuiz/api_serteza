<?php 

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Codedge\Fpdf\Fpdf\Fpdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;
use App\Exports\NumerosEnLetras;

class FacturaExport {

    public function generarReporteFactura($datos)
    {
        try{
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
                $sueldo = floatval($dato["SalarioDiarioIntegrado"]."");
                $puesto = $dato["Puesto"];
                $tipo_jornada = $dato["TipoJornada"];
                $periocidad = $dato["PeriodicidadPago"];
                $curp = $dato["Curp"];
                $num_ss = $dato["NumSeguridadSocial"];
                $sueldo_integrado = floatval($dato["SalarioBaseCotApor"]."");
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
            $vales = 0;
            $total_neto = 0;
            if($tamaño_per > $tamaño_de){
                $no_ite = $tamaño_per;
            }else{
                $no_ite = $tamaño_de;
            }
            for($i=0;$i<=$no_ite;$i++){
                $pdf->Ln(3);
                if($tamaño_per >= $i){
                    $suma = 0;
                    $pdf->Cell(70,5,$xml->xpath('//n:Percepciones/n:Percepcion')[$i]["Concepto"],0,0,"L");  
                    if($xml->xpath('//n:Percepciones/n:Percepcion')[($i)]["TipoPercepcion"] == "029"){
                        $vales += floatval($xml->xpath('//n:Percepciones/n:Percepcion')[($i)]["ImporteExento"]."");
                        $vales += floatval($xml->xpath('//n:Percepciones/n:Percepcion')[($i)]["ImporteGravado"]."");
                        $suma = floatval(($xml->xpath('//n:Percepciones/n:Percepcion')[($i)]["ImporteExento"] + $xml->xpath('//n:Percepciones/n:Percepcion')[($i)]["ImporteGravado"])."");
                        $pdf->Cell(20,5,'$'.number_format($suma,2,".",","),0,0,"R");
                        $suma_per +=  $suma;
                    }else{
                        $suma = floatval(($xml->xpath('//n:Percepciones/n:Percepcion')[($i)]["ImporteExento"] + $xml->xpath('//n:Percepciones/n:Percepcion')[($i)]["ImporteGravado"])."");
                        $pdf->Cell(20,5,'$'.number_format($suma,2,".",","),0,0,"R");
                        $suma_per +=  $suma;
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
            $pdf->Cell(38,5,"PERCEPCIONES",1,0,"C",true);
            $pdf->Cell(38,5,"DEDUCCIONES",1,0,"C",true);
            $pdf->Cell(38,5,"TOTAL NETO",1,0,"C",true);
            $pdf->Cell(38,5,"VALES",1,0,"C",true);
            $pdf->Cell(38,5,"DISPERSIÓN",1,0,"C",true);
            $pdf->Ln();
            $total = $suma_per - $suma_de;
            $total_neto = $total-$vales;
            $pdf->Cell(38,5,number_format($suma_per,2,".",","),1,0,"C");
            $pdf->Cell(38,5,number_format($suma_de,2,".",","),1,0,"C");
            $pdf->Cell(38,5,number_format($total,2,".",","),1,0,"C");
            $pdf->Cell(38,5,number_format($vales,2,".",","),1,0,"C");
            $pdf->Cell(38,5,number_format($total_neto,2,".",","),1,0,"C");
            $cuenta="";
            // if($datos[27]=="X"){$cuenta="";}else{$cuenta=$datos[27];}
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
                "ok" => "true",
                "pdf" => base64_encode($pdf->Output("S","ReciboFactura.pdf"))
            ];
        }catch(Throwable $e){
            return [
                "ok" => "false",
                "error" => "Ha ocurrido un error"
            ];
        }
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
    public function generarFactura($datos)
    {
        $xml = DB::table('fac_factura as ff')
        ->select("ff.id_catclientes","xml","observaciones","gcf.nombre","gce.empresa","iva","ieps","otros","calle","cruzamiento_uno","numero_exterior","numero_interior","colonia","localidad","gcee.estado","ff.descuento","ff.iva_r","ff.isr_r","ff.total","ff.importe","ff.subtotal","srf.clave","srf.regimenfiscal","codigo_postal", "gcc.cliente as nombre_grupo")
        ->join("gen_cat_empresa as gce","gce.id_empresa","=","ff.id_empresa")
        ->join("gen_cat_cliente as gcc", "lec.id_cliente","=","gcc.id_cliente")
        ->leftJoin("gen_cat_direccion as gcd","gcd.id_direccion","=","gce.id_direccion")
        ->leftJoin("gen_cat_estados as gcee","gcee.id_estado","=","gcd.estado")
        ->leftJoin("gen_cat_fotografia as gcf","gcf.id_fotografia","=","gce.id_fotografia")
        ->leftJoin("sat_regimenesfiscales as srf","srf.clave","=","gce.regimen_fiscal")
        ->where("id_factura",$datos)
        ->first();
        try{
            if($xml){
                $cliente = DB::table("fac_catclientes as fcc")
                ->select("calle","cruzamiento_uno","cruzamiento_dos","numero_exterior","numero_interior","colonia","localidad","gcee.estado","codigo_postal","srf.clave","srf.regimenfiscal","municipio")
                ->join("gen_cat_direccion as gcd","gcd.id_direccion","=","fcc.id_direccion")
                ->leftJoin("gen_cat_estados as gcee","gcee.id_estado","=","gcd.estado")
                ->leftJoin("sat_regimenesfiscales as srf","srf.id_regimenfiscal","=","fcc.id_regimenfiscal")
                ->where("id_catclientes",$xml->id_catclientes)
                ->first();
                $logo_empresa = storage_path('empresa')."/".$xml->nombre;
                $extension = strtoupper(explode(".",$xml->nombre)[1]);
                $nombre_empresa = $xml->empresa;
                $fecha_hora = date('d-m-Y H:i:s');
                $xml_load = simplexml_load_string($xml->xml);
                $namespaces = $xml_load->getNamespaces(true);
                $xml_load->registerXPathNamespace('c', $namespaces['cfdi']);
                $xml_load->registerXPathNamespace('t', $namespaces['tfd']);

                //Variables
                $fechatimbre ="";
                $numcer="";
                $cerSAT="";
                $sello="";
                $uuid="";
                $selloSAT="";
                $version_compro="";
                $serie = "";
                $folio ="";
                $fecha_comprobante="";
                $forma_pago="";
                $metodo_pago="";
                $moneda="";
                $selloCFD="";
                $total="";
                $total_sf = "";
                $descueto="";
                $iva=$xml->iva;
                $ieps=$xml->ieps;
                $otros=$xml->otros;
                $iva_r=$xml->iva_r;
                $isr_r=$xml->isr_r;
                $cont=40;
                //Datos emisor
                $rfc_emisor="";
                $nombre_emisor="";
                $tipo_regimen="";
                $observaciones=$xml->observaciones;
                $direccion_emisor=strtoupper($xml->calle."  #".$xml->numero_exterior." - Int. ".$xml->numero_interior." x ".$xml->cruzamiento_uno." Col. ".$xml->colonia.", ".$xml->localidad
                .", ".$xml->estado);
                //Daos receptor
                $nombre_receptor="";
                $rfc_receptor="";
                $num_interior = "";
                $num_exterior = "";
                $calle="";
                if($cliente->calle != ""){
                    $calle = "C ".$cliente->calle;
                }
                if($cliente->numero_exterior != ""){
                    $num_exterior = " No. Ext ".$cliente->numero_exterior;
                }
                if($cliente->numero_interior != ""){
                    $num_interior = ", No. Int ".$cliente->numero_interior;
                }
                $cruzamientos = "";
                if($cliente->cruzamiento_uno != "" && $cliente->cruzamiento_dos != ""){
                    $cruzamientos = " POR ".$cliente->cruzamiento_uno." Y ".$cliente->cruzamiento_dos;
                }
                if($cliente->cruzamiento_uno != ""){
                    $cruzamientos = " ".$cliente->cruzamiento_uno;
                }
                if($cliente->cruzamiento_dos != ""){
                    $cruzamientos = " ".$cliente->cruzamiento_dos;
                }
                $direccion_receptor_line_1=strtoupper($calle.$num_exterior.$num_interior.$cruzamientos);
                $direccion_receptor_line_2=strtoupper($cliente->colonia);
                $localidad = "";
                if($cliente->localidad != ""){
                    $localidad = $cliente->localidad;
                }
                $municipio = "";
                if($cliente->municipio != ""){
                    $municipio = ", ".$cliente->municipio;
                }
                $estado = "";
                if($cliente->estado != ""){
                    $estado = ", ".$cliente->estado;
                }
                $direccion_receptor_line_3=strtoupper($cliente->codigo_postal." ".$localidad.$municipio.$estado);
                // $direccion_receptor=strtoupper($cliente->calle."  #".$cliente->numero_exterior." - Int. ".$cliente->numero_interior." x ".$cliente->cruzamiento_uno." Col. ".$cliente->colonia.", ".$cliente->localidad
                // .", ".$cliente->estado);
                $uso_cfdi="";
                $subtotal = "";
                $dec="";
                foreach($xml_load->xpath('//c:Comprobante') as $dato){
                    $numcer = $dato["NoCertificado"];
                    $version_compro = $dato["Version"];
                    $serie = $dato["Serie"];
                    $folio = $dato["Folio"];
                    $fecha_comprobante = $dato["Fecha"];
                    $forma_pago = $dato["FormaPago"];
                    $metodo_pago = $dato["MetodoPago"];
                    $moneda = $dato["Moneda"];
                    $total_sf =number_format($dato["Total"]."",2,'.',',');
                    $total = number_format($dato["Total"]."",2,'.',',');
                    $descuento = number_format($dato["Descuento"]."",2,'.',',');
                    $dec=$dato["Descuento"];
                }
                
                $subtotal = ((floatval(str_replace(',','',$iva))+floatval(str_replace(',','',$ieps)))-(floatval($dec)));
                foreach($xml_load->xpath('//t:TimbreFiscalDigital') as $dato){
                    $fechatimbre = date('d-m-Y H:i:s',strtotime($dato["FechaTimbrado"]));
                    $sello = $dato["SelloCFD"];
                    $uuid = $dato["UUID"];
                    $cerSAT = $dato["NoCertificadoSAT"];
                    $selloSAT = $dato["SelloSAT"];
                    $selloCFD = $dato["SelloCFD"];
                }
                foreach($xml_load->xpath('//c:Emisor') as $dato){
                    $rfc_emisor = $dato["Rfc"];
                    $nombre_emisor = $dato["Nombre"];
                    $tipo_regimen = $dato["RegimenFiscal"];
                }
                foreach($xml_load->xpath('//c:Receptor') as $dato){
                    $rfc_receptor = $dato["Rfc"];
                    $nombre_receptor = $dato["Nombre"];
                    $direccion="";
                    $uso_cfdi = $dato["UsoCFDI"];
                }
                $uso_cfdi = DB::table('sat_UsoCFDI')->select("descripcion","clave_cfdi")->where("clave_cfdi",$uso_cfdi)->first();
                $tipo_moneda = DB::table('sat_CatMoneda')->select("descripcion","clave_moneda")->where("clave_moneda",$moneda)->first();
                $forma_pago = DB::table('sat_FormaPago')->select("descripcion","forma_pago")->where("forma_pago",$forma_pago)->first();
                $metodo_pago = DB::table('sat_MetodoPago')->select("descripcion","clave_pago")->where("clave_pago",$metodo_pago)->first();
                //Consulta api qr
                $temp_image = "image_temp_qr.png";
                $baseUrl = 'https://chart.googleapis.com/chart';
                $client = new \GuzzleHttp\Client();
                $response = $client->request('POST', $baseUrl, [
                        'form_params' => [
                            "cht" => "qr",
                            "chs" => "200x200",
                            "chl" => 'https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?&id='.$uuid.'&re='.$rfc_emisor.'&rr='.$rfc_receptor.'&tt='.$total_sf.'&fe='.substr($selloCFD,-8)
                        ]
                ]);
                $statuscode = $response->getStatusCode();
                if (200 === $statuscode) {
                    $qr_image = $response->getBody();
                }else{
                    $qr_image = file_get_contents('https://chart.googleapis.com/chart?cht=qr&chs=150x150&chl="https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx');
                }
                file_put_contents($temp_image, $qr_image);
                #region [DATOS DE LA FACTURA]
                $pdf = new PDFEdit('P','mm','A4');
                $pdf->AddPage();
                $pdf->SetFont('Helvetica','',9);
                $pdf->SetX(130);
                $pdf->SetDrawColor(221,221,221);
                $pdf->SetFillColor(221, 221, 221);
                $pdf->Cell(75,5,utf8_decode("CFDI Versión ").$version_compro,1,0,"L",true);
                $pdf->Ln();
                $pdf->SetX(130);
                $pdf->SetFont('Helvetica','B',9);
                $pdf->Cell(75,5,"Folio fiscal",1,0,"L",true);
                $pdf->Ln();
                $pdf->SetX(130);
                $pdf->SetFont('Helvetica','',9);
                $pdf->Cell(75,5,$uuid,1,0,"L",false);
                $pdf->Ln();
                $pdf->SetX(130);
                $pdf->SetFont('Helvetica','B',9);
                $pdf->Cell(75,5,utf8_decode("Número de serie del CSD"),1,0,"L",true);
                $pdf->Ln();
                $pdf->SetX(130);
                $pdf->SetFont('Helvetica','',9);
                $pdf->Cell(75,5,"$numcer",1,0,"L",false);
                $pdf->Ln();
                $pdf->SetX(130);
                $pdf->SetFont('Helvetica','B',9);
                $pdf->Cell(75,5,utf8_decode("Código postal, Fecha expedición y certificación "),1,0,"L",true);
                $pdf->Ln();
                $pdf->SetX(130);
                $pdf->SetFont('Helvetica','',9);
                $pdf->Cell(75,5,"CP: ".$xml->codigo_postal,"LRT",0,"L",false);
                $pdf->Ln();
                $pdf->SetX(130);
                $pdf->SetFont('Helvetica','',9);
                $pdf->Cell(75,5,date('d/m/Y H:m:i',strtotime($fecha_hora)) . " - " . date('d/m/Y H:m:i',strtotime($fechatimbre)),"LRB",0,"L",false);
                $pdf->Ln();
                $pdf->SetX(130);
                $pdf->SetFont('Helvetica','B',9);
                $pdf->Cell(75,5,utf8_decode("Serie, folio y uso CDFI"),1,0,"L",true);
                $pdf->Ln();
                $pdf->SetX(130);
                $pdf->SetFont('Helvetica','',9);
                $pdf->Cell(75,5,$serie." - ".$folio,'LRT',0,"L",false);
                $pdf->Ln();
                $pdf->SetX(130);
                $pdf->SetFont('Helvetica','',9);
                $pdf->Cell(75,5,$uso_cfdi->clave_cfdi ." - ".$uso_cfdi->descripcion,'LRB',0,"L",false);
                $pdf->SetY(47);
                $pdf->SetX(10);
                $pdf->Cell(120,0.5,"",0,0,"L",true);
            #endregion
            #region [NOMBRE/FOTO EMPRESA EMISORA]
                //qr
                // $pdf->Image($temp_image,170,15,30,30,'PNG','');
                // unlink($temp_image);
                
                $pdf->Image($logo_empresa,10,10,35,35,$extension,'');
                // ESTO SE AGREGO
                    $pdf->SetFont('Helvetica','B',12);
                    $pdf->SetY(20);
                    $pdf->SetX(50);
                    $pdf->SetTextColor(245,55,91);
                    $pdf->Cell(50,5,$nombre_grupo,0,0,"L");
                    $pdf->Ln();
                // ***************
                $pdf->SetFont('Helvetica','B',10);
                //$pdf->SetY(20);
                $pdf->SetX(50);
                $pdf->SetTextColor(245,55,91);
                $pdf->Cell(55,5,$nombre_empresa,0,0,"L");
                $pdf->Ln();
                $pdf->SetX(50);
                $pdf->SetTextColor(0,0,0);
                $pdf->SetFont('Helvetica','',10);
                $pdf->Cell(55,5,utf8_decode($rfc_emisor),0,0,"L");
                $pdf->Ln();
                $pdf->SetX(50);
                $pdf->SetFont('Helvetica','B',6);
                $pdf->Cell(5,5,utf8_decode($tipo_regimen),0,0,"L");
                $pdf->SetFont('Helvetica','',6);
                $pdf->SetTextColor(92,91,86);
                if(strlen($xml->regimenfiscal) > 47){
                    $pdf->Cell(5,5,utf8_decode(" - ".substr($xml->regimenfiscal,0,47)),0,0,"L");
                    $pdf->Ln();
                    //$pdf->SetY(10);
                    $pdf->SetX(55);
                    $pdf->SetFont('Helvetica','',6);
                    $pdf->SetTextColor(92,91,86);
                    $pdf->Cell(5,0,utf8_decode(" - ".substr($xml->regimenfiscal,48,strlen($xml->regimenfiscal))),0,0,"L");
                }else{
                    $pdf->Cell(5,5,utf8_decode(" - ".$xml->regimenfiscal),0,0,"L");
                }
                
            #endregion
            #region [NOMBRE EMPRESA RECEPTORA]
                $pdf->SetY(50);
                $pdf->Ln();
                $pdf->SetFont('Helvetica','B',11);
                $pdf->SetTextColor(245,60,86);
                $pdf->SetX(10);
                $pdf->Cell(40,5,$nombre_receptor,0,0,"L");
                $pdf->Ln(); 
                $pdf->SetX(10);
                $pdf->SetTextColor(0,0,0);
                $pdf->SetFont('Helvetica','',10);
                $pdf->Cell(40,5,$rfc_receptor,0,0,"L");
                $pdf->Ln(); 
                if($direccion_receptor_line_1 != ""){
                    $pdf->SetX(10);
                    $pdf->SetFont('Helvetica','',9);
                    $pdf->SetTextColor(0,0,0);
                    $pdf->Cell(100,5,utf8_decode($direccion_receptor_line_1),0,0,"L");
                    $pdf->Ln(); 
                }
                if($direccion_receptor_line_2 != ""){
                    $pdf->SetX(10);
                    $pdf->SetFont('Helvetica','',9);
                    $pdf->SetTextColor(0,0,0);
                    $pdf->Cell(100,5,utf8_decode($direccion_receptor_line_2),0,0,"L");
                    $pdf->Ln(); 
                }
                if($direccion_receptor_line_3 != ""){
                    $pdf->SetX(10);
                    $pdf->SetFont('Helvetica','',9);
                    $pdf->SetTextColor(0,0,0);
                    $pdf->Cell(100,5,utf8_decode($direccion_receptor_line_3),0,0,"L");
                    $pdf->Ln(); 
                }
                $pdf->SetX(10);
                $pdf->SetFont('Helvetica','B',9);
                $pdf->Cell(6,5,$cliente->clave,0,0,"L");
                $pdf->SetFont('Helvetica','',9);
                $pdf->SetTextColor(92,91,86);
                $pdf->Cell(20,5," - ".utf8_decode($cliente->regimenfiscal),0,0,"L");
                $pdf->Ln();
            #endregion

                #region [CONCEPTOS DE LA FACTURA]
                    $pdf->Ln();
                    $pdf->SetTextColor(0,0,0);
                    $pdf->SetFont('Arial','B',9);
                    $pdf->Cell(20,10,"Clave",1,0,"L",true);
                    $pdf->Cell(15,10,utf8_decode("Código"),1,0,"L",true);
                    $pdf->Cell(15,10,"Cantidad",1,0,"R",true);
                    $pdf->Cell(20,10,"Unidad SAT",1,0,"L",true);
                    $pdf->Cell(75,10,"Descricion",1,0,"L",true);
                    $pdf->Cell(25,10,"Precio unitario",1,0,"R",true);
                    $pdf->Cell(25,10,"Importe",1,0,"R",true);
                    $pdf->Ln();
            
            
                    $i=0;
                    $pdf->SetFont('Arial','',8);
                    $importeTotal=0;
                    foreach($xml_load->xpath('//c:Conceptos/c:Concepto') as $dato){
                        $tamaño_celda = ceil(strlen($dato["Descripcion"])/35)*4;
                        $vectorY=$pdf->GetY();
                        $vectorX=$pdf->GetX();
                        $pdf->SetFont('Arial','',9);
                        $pdf->Cell(20,$tamaño_celda,$dato["ClaveProdServ"],1,0,"L");
                        $pdf->Cell(15,$tamaño_celda,$dato["NoIdentificacion"],1,0,"L");
                        $pdf->Cell(15,$tamaño_celda,$dato['Cantidad'],1,0,"C");
                        $pdf->Cell(20,$tamaño_celda,$dato["ClaveUnidad"],1,0,false);
                        $pdf->MultiCell(75,4,utf8_decode($dato["Descripcion"]),1,"L",false);
                        $pdf->SetY($vectorY);
                        $pdf->SetX(155);
                        $pdf->Cell(25,$tamaño_celda,number_format($dato['ValorUnitario']."",2,'.',','),1,0,"R");
                        $pdf->Cell(25,$tamaño_celda,number_format($dato['Importe']."",2,'.',','),1,0,"R");
                        $importeTotal+=floatval($dato['Importe']);
                        $pdf->Ln();
                        $i++;
                        if($vectorY >= 230){
                            $pdf->AddPage();
                            $pdf->SetXY(10,10);
                            if($i < count($datos["conceptos"])){
                                $pdf->SetTextColor(0,0,0);
                                $pdf->SetFont('Arial','B',9);
                                $pdf->Cell(20,10,"Clave SAT",1,0,"L",true);
                                $pdf->Cell(15,10,utf8_decode("Código"),1,0,"L",true);
                                $pdf->Cell(15,10,"Cantidad",1,0,"R",true);
                                $pdf->Cell(20,10,"Unidad SAT",1,0,"L",true);
                                $pdf->Cell(75,10,"Descricion",1,0,"L",true);
                                $pdf->Cell(25,10,"Precio unitario",1,0,"R",true);
                                $pdf->Cell(25,10,"Importe",1,0,"R",true);
                                $pdf->Ln();
                            }
            }
                    }
                #endregion
                #region [TOTALES]
                    $subtotal = $subtotal + $importeTotal;
                    $total_value = str_replace(',','',$total);
                    $sueldo_letras = NumerosEnLetras::convertir(floatval($total_value), "", false, 'Centavos') ." ".$tipo_moneda->descripcion . " (".$tipo_moneda->clave_moneda.")";
                    $pdf->SetFont('Arial','',9);
                    $pdf->Cell(130,5,"Importe con letras",0,0,"L",false);
                    $pdf->SetFont('Arial','B',9);
                    $pdf->Cell(30,5,"Importe Total",0,0,"L",false);
                    $pdf->SetFont('Arial','',9);
                    $pdf->Cell(35,5,"$".number_format($importeTotal."",2,'.',','),0,0,"R",false);
                    $pdf->Ln();
                    $pdf->SetFont('Arial','B',9);
                    $vectorY=$pdf->GetY();
                    $vectroX=intval($pdf->GetX())+120;
                    $pdf->MultiCell(120,5,strtoupper($sueldo_letras),0,"L",false);
                    $pdf->SetXY($vectroX,$vectorY);
                    $pdf->Cell(10,5,"",0,0,"R",false);
                    $pdf->Cell(30,5,"Descuento",0,0,"L",false);
                    $pdf->SetFont('Arial','',9);
                    $pdf->Cell(35,5,"$".$descuento,0,0,"R",false);
                    $pdf->Ln();
                    $pdf->SetFont('Arial','B',9);
                    $pdf->SetX(140);
                    $pdf->Cell(30,5,"IVA 16.0%",0,0,"L",false);
                    $pdf->SetFont('Arial','',9);
                    $pdf->Cell(35,5,"$".$iva,0,0,"R",false);
                    $pdf->Ln();
                    $pdf->SetFont('Arial','B',9);
                    $pdf->SetX(140);
                    $pdf->Cell(30,5,"IEPS",0,0,"L",false);
                    $pdf->SetFont('Arial','',9);
                    $sum_otros = "";
                    $pdf->Cell(35,5,"$".$ieps,0,0,"R",false);
                    $pdf->Ln();
                    $pdf->SetFont('Arial','B',9);
                    $pdf->SetX(140);
                    $pdf->Cell(30,5,"Otros Imp.",0,0,"L",false);
                    $pdf->SetFont('Arial','',9);
                    $pdf->Cell(35,5,"$".$otros,0,0,"R",false);
                    $pdf->Ln();
                    $pdf->SetX(140);
                    $pdf->SetFont('Arial','B',9);
                    $pdf->SetFillColor(0,0,0);
                    $pdf->Cell(65,0.5,"",0,1,"R",true);
                    $pdf->Ln();
                    $pdf->SetX(140);
                    $pdf->Cell(30,5,"Subtotal",0,0,"L",false);
                    $pdf->SetFont('Arial','',9);
                    $pdf->Cell(35,5,"$".number_format($subtotal."",2,'.',','),0,0,"R",false);
                    $pdf->Ln();
                    $pdf->SetFont('Arial','B',9);
                    $pdf->SetX(140);
                    $pdf->Cell(30,5,"IVA Retenido",0,0,"L",false);
                    $pdf->SetFont('Arial','',9);
                    if(in_array($iva_r,[null,""])){
                        $iva_r = "0.00";
                    }
                    if(in_array($isr_r,[null,""])){
                        $isr_r = "0.00";
                    }
                    $pdf->Cell(35,5,"$".$iva_r,0,0,"R",false);
                    $pdf->Ln();
                    $pdf->SetFont('Arial','B',9);
                    $pdf->SetX(140);
                    $pdf->Cell(30,5,"ISR Retenido",0,0,"L",false);
                    $pdf->SetFont('Arial','',9);
                    $pdf->Cell(35,5,"$".$isr_r,0,0,"R",false);
                    $pdf->Ln();
                    $pdf->SetFillColor(213, 216, 220);
                    $pdf->SetFont('Arial','',9);
                    $pdf->Cell(130,5,"| ". $forma_pago->forma_pago." - ".utf8_decode($forma_pago->descripcion)." | ".
                    $metodo_pago->clave_pago ." - ".utf8_decode($metodo_pago->descripcion),1,0,"L",true);
                    $pdf->SetFont('Arial','B',9);
                    $pdf->Cell(30,5,"TOTAL",1,0,"L",true);
                    $pdf->SetFont('Arial','',9); 
                    $pdf->Cell(35,5,"$".$total,0,1,"R",true);
                    ////
                    //FIN de Obtencion de los valores totales de la factura
                    ////
                #endregion
                #region [CADENAS SAT, QR]
                    $pdf->Ln();
                    $pdf->Image($temp_image,170,$pdf->GetY()-2,30,30,'PNG','');
                    unlink($temp_image);
                    $pdf->SetFont('Helvetica','B',8);
                    $pdf->Cell(160,5,utf8_decode("Observaciones"),0,0,"L");
                    $pdf->SetFont('Helvetica','',8);
                    $pdf->Ln();
                    $pdf->MultiCell(160,4,utf8_decode($observaciones),0,"L",false);
                    $pdf->SetFont('Helvetica','B',8);
                    $pdf->Cell(160,5,utf8_decode("Cadena Original del Complemento de Certificación Digital del SAT"),0,0,"L");
                    $pdf->SetFont('Helvetica','',8);
                    $pdf->Ln();
                    $pdf->MultiCell(160,5,utf8_decode("||1.0|".$uuid."|".$fechatimbre."|".$selloCFD."|".$numcer."||"));
                    $pdf->SetFont('Helvetica','B',8);
                    $pdf->Cell(180,5,utf8_decode("Sello Digital del Emisor"),0,0,"L");
                    $pdf->SetFont('Helvetica','',8);
                    $pdf->Ln();
                    $pdf->MultiCell(190,5,utf8_decode($selloCFD));
                    $pdf->SetFont('Helvetica','B',8);
                    $pdf->Cell(180,5,utf8_decode("Sello Digital SAT"),0,0,"L");
                    $pdf->SetFont('Helvetica','',8);
                    $pdf->Ln();
                    $pdf->MultiCell(190,5,utf8_decode($selloSAT));
                #endregion
                return ["ok" => true, "data" => base64_encode($pdf->Output("S","ReporteFactura.pdf"))];
            }
            // return ["ok" => false, "message" => "No se ha podido recuperar el XML"];
        }catch(Trowable $e){
            return ["ok" => false, "message" => "Error al cargar el XML"];
        }

    }
    public function generaFacturaPreview($datos)
    {
        $xml = DB::table('gen_cat_empresa as gce')
        ->select("gcf.nombre","gce.empresa","no_certificado","gce.rfc","gce.regimen_fiscal","srf.regimenfiscal","dir.codigo_postal")
        ->leftJoin("gen_cat_direccion as dir","gce.id_direccion","=","dir.id_direccion")
        ->leftJoin("sat_regimenesfiscales as srf","gce.regimen_fiscal","=","srf.clave")
        ->leftJoin("gen_cat_fotografia as gcf","gcf.id_fotografia","=","gce.id_fotografia")
        ->where("id_empresa",$datos["id_empresa"])
        ->first();
        $cliente = DB::table('fac_catclientes as fc')
        ->select("fc.rfc","fc.razon_social","dir.calle","dir.numero_interior","dir.numero_exterior","dir.cruzamiento_uno","dir.cruzamiento_dos","dir.codigo_postal",
        "dir.colonia","dir.localidad","dir.municipio","est.estado","srf.regimenfiscal","srf.clave")
        ->leftJoin("gen_cat_direccion as dir","fc.id_direccion","=","dir.id_direccion")
        ->leftJoin("gen_cat_estados as est","dir.estado","=","est.id_estado")
        ->leftJoin("sat_regimenesfiscales as srf","fc.id_regimenfiscal","=","srf.id_regimenfiscal")
        ->where("id_catclientes",$datos["id_cliente"])
        ->first();
        $serie = DB::table('fac_catseries')->select("serie")->where("id_serie",$datos["id_serie"])->first()->serie;
        //Variables
        $logo_empresa = storage_path('empresa')."/".$xml->nombre;
        $extension = strtoupper(explode(".",$xml->nombre)[1]);
        $nombre_empresa = $xml->empresa;
        $fecha_hora = date('d-m-Y H:i:s');
        $fechatimbre =date('d-m-Y');
        $numcer=$xml->no_certificado;
        $cerSAT="";
        $sello="";
        $uuid="";
        $selloSAT="";
        $version_compro="";
        if(in_array($datos["id_empresa"],[106,107,108,105,56,81])){
            $version_compro = "4.0";
        }else{
            $version_compro = "3.3";
        }
        $uso_cfdi=$datos["id_usocfdi"];
        $forma_pago=$datos["id_formapago"];
        $metodo_pago=$datos["id_metodopago"];
        $moneda=$datos["id_tipomoneda"];
        $tipo_moneda = DB::table('sat_CatMoneda')->select("descripcion","clave_moneda")->where("id_catmoneda",$moneda)->first();
        $forma_pago = DB::table('sat_FormaPago')->select("descripcion","forma_pago")->where("id_formapago",$forma_pago)->first();
        $metodo_pago = DB::table('sat_MetodoPago')->select("descripcion","clave_pago")->where("id_metodopago",$metodo_pago)->first();
        $uso_cfdi = DB::table('sat_UsoCFDI')->select("descripcion","clave_cfdi")->where("id_usocfdi",$uso_cfdi)->first();

        // $serie = $datos["serie"];
        $folio =$datos["folio"];
        $fecha_comprobante="";
        
        $selloCFD="";
        $importe = $datos["importe"];
        $descuento=$datos["descuento_t"];
        $iva=$datos["iva_t"];
        $ieps=$datos["ieps_t"];
        $otros=$datos["otros_t"];
        $subtotal=$datos["subtotal"];
        $iva_r = $datos["iva_r_t"];
        $isr_r=$datos["isr_r_t"];
        $total=$datos["total"];
        $cont=40;
        //Datos emisor
        $rfc_emisor=$xml->rfc;
        $nombre_emisor=$xml->empresa;
        $tipo_regimen=$xml->regimen_fiscal;
        $direccion_emisor="";
        //Daos receptor
        $nombre_receptor=$cliente->razon_social;
        $rfc_receptor=$cliente->rfc;
        $num_interior = "";
        $num_exterior = "";
        $calle="";
        if($cliente->calle != ""){
            $num_interior = "C ".$cliente->calle;
        }
        if($cliente->numero_exterior != ""){
            $num_interior = " No. Ext ".$cliente->numero_exterior;
        }
        if($cliente->numero_interior != ""){
            $num_interior = ", No. Int ".$cliente->numero_interior;
        }
        $cruzamientos = "";
        if($cliente->cruzamiento_uno != "" && $cliente->cruzamiento_dos != ""){
            $cruzamientos = " POR ".$cliente->cruzamiento_uno." Y ".$cliente->cruzamiento_dos;
        }
        if($cliente->cruzamiento_uno != ""){
            $cruzamientos = " ".$cliente->cruzamiento_uno;
        }
        if($cliente->cruzamiento_dos != ""){
            $cruzamientos = " ".$cliente->cruzamiento_dos;
        }
        $direccion_receptor_line_1=strtoupper($calle.$num_exterior.$num_interior.$cruzamientos);
        $direccion_receptor_line_2=strtoupper($cliente->colonia);
        $localidad = "";
        if($cliente->localidad != ""){
            $localidad = $cliente->localidad;
        }
        $municipio = "";
        if($cliente->municipio != ""){
            $municipio = ", ".$cliente->municipio;
        }
        $estado = "";
        if($cliente->estado != ""){
            $estado = ", ".$cliente->estado;
        }
        $direccion_receptor_line_3=strtoupper($cliente->codigo_postal." ".$localidad.$municipio.$estado);
        //Consulta api qr
        $temp_image = "image_temp_qr.png";
        $baseUrl = 'https://chart.googleapis.com/chart';
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', $baseUrl, [
                'form_params' => [
                    "cht" => "qr",
                    "chs" => "200x200",
                    "chl" => 'https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?&id='.$uuid.'&re='.$rfc_emisor.'&rr='.$rfc_receptor.'&tt='.''.'&fe='.substr($selloCFD,-8)
                ]
        ]);
        $statuscode = $response->getStatusCode();
        if (200 === $statuscode) {
            $qr_image = $response->getBody();
        }else{
            $qr_image = file_get_contents('https://chart.googleapis.com/chart?cht=qr&chs=150x150&chl="https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx');
        }
        file_put_contents($temp_image, $qr_image);
        //carga pdf
        $pdf = new PDFEdit('P','mm','A4');
        $pdf->AddPage();
        $pdf->Image($logo_empresa,10,10,35,35,$extension,'');
        $pdf->SetFont('Helvetica','',9);
        $pdf->SetX(140);
        $pdf->SetDrawColor(221,221,221);
        $pdf->SetFillColor(221, 221, 221);
        $pdf->Cell(65,5,utf8_decode("CFDI Versión ").$version_compro,1,0,"L",true);
        $pdf->Ln();
        $pdf->SetX(140);
        $pdf->SetFont('Helvetica','B',9);
        $pdf->Cell(65,5,"Folio fiscal",1,0,"L",true);
        $pdf->Ln();
        $pdf->SetX(140);
        $pdf->SetFont('Helvetica','',9);
        $pdf->Cell(65,5,"No disponible",1,0,"L",false);
        $pdf->Ln();
        $pdf->SetX(140);
        $pdf->SetFont('Helvetica','B',9);
        $pdf->Cell(65,5,utf8_decode("Número de serie del CSD"),1,0,"L",true);
        $pdf->Ln();
        $pdf->SetX(140);
        $pdf->SetFont('Helvetica','',9);
        $pdf->Cell(65,5,"$numcer",1,0,"L",false);
        $pdf->Ln();
        $pdf->SetX(140);
        $pdf->SetFont('Helvetica','B',9);
        $pdf->Cell(65,5,utf8_decode("Código postal, fecha y hora de emisión"),1,0,"L",true);
        $pdf->Ln();
        $pdf->SetX(140);
        $pdf->SetFont('Helvetica','',9);
        $pdf->Cell(65,5,"CP: ".$xml->codigo_postal,"LRT",0,"L",false);
        $pdf->Ln();
        $pdf->SetX(140);
        $pdf->SetFont('Helvetica','',9);
        $pdf->Cell(65,5,date('d/m/Y H:m:i',strtotime($fechatimbre)),"LRB",0,"L",false);
        $pdf->Ln();
        $pdf->SetX(140);
        $pdf->SetFont('Helvetica','B',9);
        $pdf->Cell(65,5,utf8_decode("Serie, folio y uso CDFI"),1,0,"L",true);
        $pdf->Ln();
        $pdf->SetX(140);
        $pdf->SetFont('Helvetica','',9);
        $pdf->Cell(65,5,$serie." - ".$datos["folio"],'LRT',0,"L",false);
        $pdf->Ln();
        $pdf->SetX(140);
        $pdf->SetFont('Helvetica','',9);
        $pdf->Cell(65,5,$uso_cfdi->clave_cfdi ." - ".$uso_cfdi->descripcion,'LRB',0,"L",false);
        $pdf->SetY(47);
        $pdf->SetX(10);
        $pdf->Cell(130,0.5,"",0,0,"L",true);

        $pdf->SetFont('Helvetica','B',16);
        $pdf->SetY(20);
        $pdf->SetX(50);
        $pdf->SetTextColor(245,55,91);
        $pdf->Cell(50,5,$nombre_empresa,0,0,"L");
        $pdf->Ln();
        $pdf->SetX(50);
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('Helvetica','',12);
        $pdf->Cell(55,5,utf8_decode($rfc_emisor),0,0,"L");
        $pdf->Ln();
        $pdf->SetX(50);
        $pdf->SetFont('Helvetica','B',9);
        $pdf->Cell(5,5,utf8_decode($tipo_regimen),0,0,"L");
        $pdf->SetFont('Helvetica','',9);
        $pdf->SetTextColor(92,91,86);
        $pdf->Cell(5,5,utf8_decode(" - ".$xml->regimenfiscal),0,0,"L");

        $pdf->SetY(45);
        $pdf->Ln();
        $pdf->SetFont('Helvetica','B',11);
        $pdf->SetTextColor(245,60,86);
        $pdf->SetX(10);
        $pdf->Cell(40,5,$nombre_receptor,0,0,"L");
        $pdf->Ln(); 
        $pdf->SetX(10);
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('Helvetica','',10);
        $pdf->Cell(40,5,$rfc_receptor,0,0,"L");
        $pdf->Ln(); 
        if($direccion_receptor_line_1 != ""){
            $pdf->SetX(10);
            $pdf->SetFont('Helvetica','',9);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(100,5,utf8_decode($direccion_receptor_line_1),0,0,"L");
            $pdf->Ln(); 
        }
        if($direccion_receptor_line_2 != ""){
            $pdf->SetX(10);
            $pdf->SetFont('Helvetica','',9);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(100,5,utf8_decode($direccion_receptor_line_2),0,0,"L");
            $pdf->Ln(); 
        }
        if($direccion_receptor_line_3 != ""){
            $pdf->SetX(10);
            $pdf->SetFont('Helvetica','',9);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(100,5,utf8_decode($direccion_receptor_line_3),0,0,"L");
            $pdf->Ln(); 
        }
        $pdf->SetX(10);
        $pdf->SetFont('Helvetica','B',9);
        $pdf->Cell(6,5,$cliente->clave,0,0,"L");
        $pdf->SetFont('Helvetica','',9);
        $pdf->SetTextColor(92,91,86);
        $pdf->Cell(20,5," - ".$cliente->regimenfiscal,0,0,"L");
        $pdf->Ln();
        $pdf->Ln();


        

        /////
        ///Obtencion de datos contables de la factura------------------
        /////
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('Arial','B',9);
        $pdf->Cell(20,10,"Clave SAT",1,0,"L",true);
        $pdf->Cell(15,10,utf8_decode("Código"),1,0,"L",true);
        $pdf->Cell(15,10,"Cantidad",1,0,"R",true);
        $pdf->Cell(20,10,"Unidad SAT",1,0,"L",true);
        $pdf->Cell(75,10,"Descricion",1,0,"L",true);
        $pdf->Cell(25,10,"Precio unitario",1,0,"R",true);
        $pdf->Cell(25,10,"Importe",1,0,"R",true);
        $pdf->Ln();


        $i=0;
        foreach($datos["conceptos"] as $dato){
            $concepto = DB::table('fac_catconceptos as fc')
            ->select("ClaveProdServ","sum.ClaveUnidad","clave_producto")
            ->join("sat_ClaveProdServ as scp","fc.id_ClaveProdServ","=","scp.id_ClaveProdServ")
            ->join("sat_UnidadMedida as sum","fc.id_UnidadMedida","=","sum.id_UnidadMedida")
            ->where("id_concepto_empresa",$dato["id_concepto"])
            ->first();
            $tamaño_celda = ceil(strlen($dato["descripcion"])/35)*4;
            $vectorY=$pdf->GetY();
            $vectorX=$pdf->GetX();
            $pdf->SetFont('Arial','',9);
            $pdf->Cell(20,$tamaño_celda,$concepto->ClaveProdServ,1,0,"L");
            $pdf->Cell(15,$tamaño_celda,$concepto->clave_producto,1,0,"L");
            $pdf->Cell(15,$tamaño_celda,$dato['cantidad'],1,0,"C");
            $pdf->Cell(20,$tamaño_celda,$concepto->ClaveUnidad,1,0,false);
            $pdf->MultiCell(75,4,utf8_decode($dato["descripcion"]),1,"L",false);
            $pdf->SetY($vectorY);
            $pdf->SetX(155);
            $pdf->Cell(25,$tamaño_celda,number_format($dato['precio']."",2,'.',','),1,0,"R");
            $pdf->Cell(25,$tamaño_celda,number_format($dato['importe']."",2,'.',','),1,0,"R");
            $pdf->Ln();
            $i++;
            if($vectorY >= 230){
                $pdf->AddPage();
                $pdf->SetXY(10,10);
                if($i < count($datos["conceptos"])){
                    $pdf->SetTextColor(0,0,0);
                    $pdf->SetFont('Arial','B',9);
                    $pdf->Cell(20,10,"Clave SAT",1,0,"L",true);
                    $pdf->Cell(15,10,utf8_decode("Código"),1,0,"L",true);
                    $pdf->Cell(15,10,"Cantidad",1,0,"R",true);
                    $pdf->Cell(20,10,"Unidad SAT",1,0,"L",true);
                    $pdf->Cell(75,10,"Descricion",1,0,"L",true);
                    $pdf->Cell(25,10,"Precio unitario",1,0,"R",true);
                    $pdf->Cell(25,10,"Importe",1,0,"R",true);
                    $pdf->Ln();
                }
                
            }
        }

        ////
        //Obtencion de los valores totales de la factura
        ////


        
        // $pdf->SetX(140);
        $total_value = str_replace(',','',$total);
        $sueldo_letras = NumerosEnLetras::convertir(floatval($total_value), "", false, 'Centavos') ." ".$tipo_moneda->descripcion . " (".$tipo_moneda->clave_moneda.")";
        $pdf->SetFont('Arial','',9);
        $pdf->Cell(130,5,"Importe con letras",0,0,"L",false);
        $pdf->SetFont('Arial','B',9);
        $pdf->Cell(30,5,"Importe Total",0,0,"L",false);
        $pdf->SetFont('Arial','',9);
        $pdf->Cell(35,5,"$".$importe,0,0,"R",false);
        $pdf->Ln();
        $pdf->SetFont('Arial','B',9);
        $vectorY=$pdf->GetY();
        $vectroX=intval($pdf->GetX())+120;
        $pdf->MultiCell(120,5,strtoupper($sueldo_letras),0,"L",false);
        $pdf->SetXY($vectroX,$vectorY);
        $pdf->Cell(10,5,"",0,0,"R",false);
        $pdf->Cell(30,5,"Descuento",0,0,"L",false);
        $pdf->SetFont('Arial','',9);
        $pdf->Cell(35,5,"$".$descuento,0,0,"R",false);
        $pdf->Ln();
        $pdf->SetFont('Arial','B',9);
        $pdf->SetX(140);
        $pdf->Cell(30,5,"IVA 16.0%",0,0,"L",false);
        $pdf->SetFont('Arial','',9);
        $pdf->Cell(35,5,"$".$iva,0,0,"R",false);
        $pdf->Ln();
        $pdf->SetFont('Arial','B',9);
        $pdf->SetX(140);
        $pdf->Cell(30,5,"IEPS",0,0,"L",false);
        $pdf->SetFont('Arial','',9);
        $sum_otros = "";
        $pdf->Cell(35,5,"$".$ieps,0,0,"R",false);
        $pdf->Ln();
        $pdf->SetFont('Arial','B',9);
        $pdf->SetX(140);
        $pdf->Cell(30,5,"Otros Imp.",0,0,"L",false);
        $pdf->SetFont('Arial','',9);
        $pdf->Cell(35,5,"$".$otros,0,0,"R",false);
        $pdf->Ln();
        $pdf->SetX(140);
        $pdf->SetFont('Arial','B',9);
        $pdf->SetFillColor(0,0,0);
        $pdf->Cell(65,0.5,"",0,1,"R",true);
        $pdf->Ln();
        $pdf->SetX(140);
        $pdf->Cell(30,5,"Subtotal",0,0,"L",false);
        $pdf->SetFont('Arial','',9);
        $pdf->Cell(35,5,"$".$subtotal,0,0,"R",false);
        $pdf->Ln();
        $pdf->SetFont('Arial','B',9);
        $pdf->SetX(140);
        $pdf->Cell(30,5,"IVA Retenido",0,0,"L",false);
        $pdf->SetFont('Arial','',9);
        $pdf->Cell(35,5,"$".$iva_r,0,0,"R",false);
        $pdf->Ln();
        $pdf->SetFont('Arial','B',9);
        $pdf->SetX(140);
        $pdf->Cell(30,5,"ISR Retenido",0,0,"L",false);
        $pdf->SetFont('Arial','',9);
        $pdf->Cell(35,5,"$".$isr_r,0,0,"R",false);
        $pdf->Ln();
        $pdf->SetFillColor(213, 216, 220);
        $pdf->SetFont('Arial','',9);
        $pdf->Cell(130,5,"| ". $forma_pago->forma_pago." - ".utf8_decode($forma_pago->descripcion)." | ".
        $metodo_pago->clave_pago ." - ".utf8_decode($metodo_pago->descripcion),1,0,"L",true);
        $pdf->SetFont('Arial','B',9);
        $pdf->Cell(30,5,"TOTAL",1,0,"L",true);
        $pdf->SetFont('Arial','',9); 
        $pdf->Cell(35,5,"$".$total,0,1,"R",true);
        $observaciones=$datos["observaciones"];
        ////
        //FIN de Obtencion de los valores totales de la factura
        ////
        $cont=$cont+10;
        $cont=$cont+15;

        ////
        //Imprimir datos en el pie de la factura
        ////
        $pdf->Ln();
        $pdf->Image($temp_image,170,$pdf->GetY()-2,30,30,'PNG','');
        unlink($temp_image);
        $pdf->SetFont('Helvetica','B',8);
        $pdf->Cell(160,5,utf8_decode("Observaciones"),0,0,"L");
        $pdf->SetFont('Helvetica','',8);
        $pdf->Ln();
        $pdf->MultiCell(160,4,utf8_decode($observaciones),0,"L",false);
        $pdf->SetFont('Helvetica','B',8);
        $pdf->Cell(160,5,utf8_decode("Cadena Original del Complemento de Certificación Digital del SAT"),0,0,"L");
        $pdf->SetFont('Helvetica','',8);
        $pdf->Ln();
        $pdf->MultiCell(160,5,utf8_decode("||1.0|B6820FAE-0B5D-4BB7-BFD6-E990E8995330|15-03-202313:41:19|aEjrUetuYxoF6kHLWrqDfEE9XYczMgrOQV6LDvjynqDpKD6bcESp421lpt4VYcPgrcoQsyfPgv26cssJkwXyYJrhVMlhUtY9Ic9M4TOXVQTa6fuOnzFw6RScRNCHxVwb1roORJZiJTbET93lO0aCnhjR6XQw9XH24D6HqZ7275KGftmSUqW1jvWSR6AqBBH0pniSF9lwnmOl1ZBzWbp65RUJZDf6FpvZcf0ZNHApLCvNO1Yi6ocimYJ1fbL8obTmKjFHOv9d9db1bM8nHMqm2dWt6yhoXQ7LutVlpcvlFDHgemUVNaFhmW+uI0UlONA==|00001000000505236640||"));

        $pdf->SetFont('Helvetica','B',8);
        $pdf->Cell(180,5,utf8_decode("Sello Digital del Emisor"),0,0,"L");

        $pdf->SetFont('Helvetica','',8);
        $pdf->Ln();
        $pdf->MultiCell(190,5,utf8_decode('CaEjrUetuYxoF6kHLWrqDfEE9XYczMgrOQV6+LDvjynqDpKD6bcESp421lpt4VYcPgrcoQsyfPgv26css+JkwXyYJrhVM/lhUt/Y9Ic9M4TOXVQTa6fuOnz/Fw6RScRNCHxVwb1roO/RbETgsdSgfv6cpCO93lO0aCnhjR6XQw9XH24D6HqZ7275KGftmSUqW1jvWSR6AqBBH0pniSF9lwnmOl1ZBzWbp65DRUJZDf6FpvZcf0ZNHApLCvNO1Yi6ocimYJ1fbL8obTmKjFHOv9d9db1bM8nHMqm2dWt6yhoXQ7LutVlpcvlFDHgemUVNaFhmW+uI0UlONA=='));

        $pdf->SetFont('Helvetica','B',8);
        $pdf->Cell(180,5,utf8_decode("Sello Digital SAT"),0,0,"L");

        $pdf->SetFont('Helvetica','',8);
        $pdf->Ln();
        $pdf->MultiCell(190,5,utf8_decode("mWRbrnr9VubhVAWyKqHVlEulDYQOOj25ut9GvfouRSiWiDjBMxGppxf9iJqyqHfyHpOldiCxnp4r7Q2URTSQ1/XHkPXnrfGo7KWbrTBvvRwQrgqmNn+pCWo7KM1wDLsPucS4AQVuVzRWNhOlgUFwdq
MMOwYyCKQHfQvnSCG0y98PZ1QxhejU2/xYS2DHqP9Tu/WasNCBuNJLFglQJbo9XMLz0tGBbkkBgGoznTsME7x5V6X7+d4yJwzznfSuH5Am+IYUULmBuZRr7e/2o1l77e9uPWzRX7HvHa8ETYDqI7T
GEMxjk9sgbzXFGk8AmrS1LXpZtoCJ52GUn2SGxes9AQ=="));
        // $pdf->Image($folio.".jpg",170,15,30,30,'JPG','');
        return ["ok" => true, "data" => base64_encode($pdf->Output("S","ReporteFactura.pdf"))];
    }
}

class PDFEdit extends Fpdf
{
    // Pie de página
    function Footer()
    {
        // Posición: a 1,5 cm del final
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Helvetica','',8);
        // Número de página
        $this->Cell(0,10,utf8_decode('Página. ').$this->PageNo(),0,0,'R');
    }
} 