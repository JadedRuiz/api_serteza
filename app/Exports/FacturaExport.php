<?php 

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Codedge\Fpdf\Fpdf\Fpdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Storage;

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
        ->select("ff.id_catclientes","xml","observaciones","gcf.nombre","gce.empresa","iva","ieps","otros","calle","cruzamiento_uno","numero_exterior","numero_interior","colonia","localidad","gcee.estado")
        ->join("gen_cat_empresa as gce","gce.id_empresa","=","ff.id_empresa")
        ->leftJoin("gen_cat_direccion as gcd","gcd.id_direccion","=","gce.id_direccion")
        ->leftJoin("gen_cat_estados as gcee","gcee.id_estado","=","gcd.estado")
        ->leftJoin("gen_cat_fotografia as gcf","gcf.id_fotografia","=","gce.id_fotografia")
        ->where("id_factura",$datos)
        ->first();
        try{
            if($xml){
                $cliente = DB::table("fac_catclientes as fcc")
                ->select("calle","cruzamiento_uno","numero_exterior","numero_interior","colonia","localidad","gcee.estado")
                ->join("gen_cat_direccion as gcd","gcd.id_direccion","=","fcc.id_direccion")
                ->leftJoin("gen_cat_estados as gcee","gcee.id_estado","=","gcd.estado")
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
                $subtotal="";
                $descueto="";
                $iva="$ ".$xml->iva;
                $ieps="$ ".$xml->ieps;
                $otros="$".$xml->otros;
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
                $direccion_receptor=strtoupper($cliente->calle."  #".$cliente->numero_exterior." - Int. ".$cliente->numero_interior." x ".$cliente->cruzamiento_uno." Col. ".$cliente->colonia.", ".$cliente->localidad
                .", ".$cliente->estado);
                $uso_cfdi="";
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
                    $total = "$ " . number_format($dato["Total"]."",2,'.',',');
                    $subtotal = "$ " . number_format($dato["SubTotal"]."",2,'.',',');
                    $descuento = "$ " . number_format($dato["Descuento"]."",2,'.',',');
                }
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
                //Consulta api qr
                $temp_image = "image_temp_qr.png";
                $baseUrl = 'https://chart.googleapis.com/chart';
                $client = new Client();
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
                //carga pdf
                $pdf = new Fpdf('P','mm','A4');
                $pdf->AddPage();
                $pdf->SetFont('Arial','B',15);
                $pdf->Cell(40,5,"",0,0,"L");
                $pdf->Cell(70,5,$nombre_empresa,0,0,"L");
                $pdf->Ln();
    
                $pdf->SetFont('Arial','B',7);
                $pdf->Cell(40,5,"",0,0,"L");
                $pdf->Cell(55,5,utf8_decode("Fecha y hora de Expedición"),0,0,"L");
                $pdf->Cell(55,5,$fecha_hora,0,0,"L");
                $pdf->Ln();
                $pdf->Cell(40,5,"",0,0,"L");
                $pdf->Cell(55,5,utf8_decode("Número de Serie de Certificado del Emisor"),0,0,"L");
                $pdf->Cell(55,5,$numcer,0,0,"L");
                $pdf->Ln();
                $pdf->Cell(40,5,"",0,0,"L");
                $pdf->Cell(55,5,utf8_decode("Número de Serie de Certificado del SAT"),0,0,"L");
                $pdf->Cell(55,5,$cerSAT,0,0,"L");
                $pdf->Ln();
                $pdf->Cell(40,5,"",0,0,"L");
                $pdf->Cell(55,5,utf8_decode("Folio Fiscal"),0,0,"L");
                $pdf->Cell(55,5,$uuid,0,0,"L");
                $pdf->Ln();
                $pdf->Cell(40,5,"",0,0,"L");
                $pdf->Cell(55,5,utf8_decode("Fecha y hora de Certificación"),0,0,"L");
                $pdf->Cell(55,5,$fechatimbre,0,0,"L");
                $pdf->Ln();
                $pdf->Cell(40,5,"",0,0,"L");
                $pdf->Cell(55,5,utf8_decode("Versión del Comprobante"),0,0,"L");
                $pdf->Cell(55,5,$version_compro[0],0,0,"L");
                $pdf->Ln();
                $pdf->SetFont('Arial','B',12);
                $pdf->SetFillColor(213, 216, 220);
                $pdf->SetDrawColor(213, 216, 220);
    
                 $pdf->Image($logo_empresa,10,10,35,35,$extension,'');
                $pdf->Ln(); 
                
                $pdf->SetFont('Arial','B',8);
                $pdf->Cell(105,5,"Datos del Receptor",1,0,"C",true);
                $pdf->Cell(5,5,"",0,0,"L");
                $pdf->Cell(80,5,"Datos de Factura",1,0,"C",true);
                $pdf->Ln(); 
                $pdf->SetFont('Arial','',7);
                $pdf->Cell(20,5,"RFC",1,0,"L");
                $pdf->Cell(85,5,$rfc_receptor,1,0,"L");
                $pdf->Cell(5,5,"",0,0,"L");
                $pdf->Cell(25,5,"Serie y Folio",1,0,"L");
                $pdf->Cell(55,5,$serie." ".$folio,1,0,"L");
                $pdf->Ln(); 
                $pdf->Cell(20,5,"Nombre",1,0,"L");
                $pdf->Cell(85,5,$nombre_receptor,1,0,"L");
                $pdf->Cell(5,5,"",0,0,"L");
                $pdf->Cell(25,5,"Fecha",1,0,"L");
                $pdf->Cell(55,5,$fecha_comprobante,1,0,"L");
                $pdf->Ln(); 
                $pdf->Cell(20,5,utf8_decode("Dirección"),1,0,"L");
    
                $numcaracteres=strlen(utf8_decode($direccion_receptor));
                if($numcaracteres>58){
                $direc1 = substr(utf8_decode($direccion_receptor),0,58);
                $direc2 = substr(utf8_decode($direccion_receptor),58,57);
                $direc3 = substr(utf8_decode($direccion_receptor),115,$numcaracteres);
                }else{
                $direc1=utf8_decode($direccion_receptor);
                $direc2="";
                $direc3="";
                }
    
                $pdf->Cell(85,5,$direc1,1,0,"L");
                $pdf->Cell(5,5,"",0,0,"C");
                $pdf->Cell(25,5,"Cert CSD",1,0,"L");
                $pdf->Cell(55,5,$numcer,1,0,"L");
                $pdf->Ln();
                $pdf->Cell(20,5,"",1,0,"L");
                $pdf->Cell(80,5,$direc2,0,0,"L");
                $pdf->Cell(5,5,"",0,0,"L");
                $pdf->Cell(5,5,"",0,0,"C");
    
                $forma_pago = DB::table('sat_FormaPago')->select("descripcion")->where("forma_pago",$forma_pago)->first();
                $pdf->Cell(25,5,utf8_decode("Forma de Pago"),1,0,"L");///////////////////////////////////////////////////////////sdfgsdfrgsdrfgsdfghsdfsdgfsdfgsdfsdf
    
    
                // $respnc = $conexion->ejecutarconsulta("SELECT NumeroCuenta From tim_encfacturas WHERE FacturaID =$FacturaID");
                // $dnc = $respnc->fetch_array(MYSQLI_ASSOC);
                $pdf->Cell(55,5,utf8_decode($forma_pago->descripcion),1,0,"L");
                $pdf->Ln();
    
                $pdf->Cell(20,5,"",1,0,"L");
                $pdf->Cell(85,5,$direc3,1,0,"L");
                //$pdf->Cell(20,5,"utf8_decode("N° de Cuenta")",1,0,"L");
                //$pdf->Cell(85,5,($dnc['NumeroCuenta']==0?"":$dnc['NumeroCuenta']),1,0,"L");
    
                $pdf->Cell(5,5,"",0,0,"L");
                $pdf->Cell(25,5,utf8_decode("Método de Pago"),1,0,"L");///////////////////////////////////////////////////////////sdfgsdfrgsdrfgsdfghsdfsdgfsdfgsdfsdf
    
    
                $metodo_pago = DB::table('sat_MetodoPago')->select("descripcion")->where("clave_pago",$metodo_pago)->first();
                $pdf->Cell(55,5,utf8_decode($metodo_pago->descripcion),1,0,"L");
                $pdf->Ln();
                $uso_cfdi = DB::table('sat_UsoCFDI')->select("descripcion")->where("clave_cfdi",$uso_cfdi)->first();
                $pdf->Cell(20,5,"Uso CFDI",1,0,"L");
                $pdf->Cell(85,5,utf8_decode($uso_cfdi->descripcion),1,0,"L");
                $pdf->Cell(5,5,"",0,0,"L");
                $pdf->Cell(25,5,utf8_decode("Tipo de Moneda"),1,0,"L");///////////////////////////////////////////////////////////sdfgsdfrgsdrfgsdfghsdfsdgfsdfgsdfsdf
    
    
                $tipo_moneda = DB::table('sat_CatMoneda')->select("descripcion")->where("clave_moneda",$moneda)->first();
                $pdf->Cell(55,5,$tipo_moneda->descripcion ."(".$moneda.")",1,0,"L");
                $pdf->Ln();
    
                $pdf->Cell(105,5,"",0,0,"L",true);
                $pdf->Cell(5,5,"",0,0,"L");
                $pdf->Cell(80,5,"",0,0,"L",true);
                $pdf->Ln();
                $pdf->Ln();
                //$pdf->SetXY(310,10);          // Primero establece Donde estará la esquina superior izquierda donde estará tu celda 
                //$pdf->SetTextColor(255,255,255);  // Establece el color del texto (en este caso es blanco) 
                // establece el color del fondo de la celda (en este caso es AZUL 
    
                $pdf->SetFont('Arial','B',8);
                ///Imprimir resultados--------
                $pdf->Cell(190,5,"Datos del Emisor",1,0,"C",true);
                $pdf->SetFont('Arial','',7);
                $pdf->Ln();
                $pdf->Cell(190,5,$rfc_emisor,0,0,"C");
                $pdf->Ln();
                $pdf->Cell(190,5,$nombre_emisor,0,0,"C");
                $pdf->Ln();
                $pdf->Cell(190,5,"Expedido: ".$direccion_emisor,0,0,"C");
                $pdf->Ln();
                $pdf->Cell(190,5,"Tipo Regimen: ".$tipo_regimen,0,0,"C");
                $pdf->Ln();
    
                $pdf->Cell(190,1,"",1,0,"L",true);
                $pdf->Cell(5,5,"",0,0,"L");
                $pdf->Cell(80,5,"",0,0,"C");
                $pdf->SetFont('Arial','B',8);
    
                /////
                ///Obtencion de datos contables de la factura------------------
                /////
                $pdf->Ln();
                $pdf->Cell(15,10,"Codigo",1,0,"L",true);
                $pdf->Cell(15,10,"ClaveProdSer",1,0,"L",true);
                $pdf->Cell(65,10,"Descricion",1,0,"L",true);
                $pdf->Cell(35,10,"Unidad",1,0,"L",true);
                $pdf->Cell(25,10,"Precio",1,0,"R",true);
                $pdf->Cell(15,10,"Cantidad",1,0,"R",true);
                $pdf->Cell(20,10,"Importe",1,0,"R",true);
                $pdf->Ln();
    
    
                $i=0;
                $pdf->SetFont('Arial','',6);
                $cont_h = 1;
                $y=135;
				$contadorconceptos = 0;
				$conceptosimprimir = 13;
				$NumeroPagina = 1;
                foreach($xml_load->xpath('//c:Conceptos/c:Concepto') as $dato){
					if($contadorconceptos > $conceptosimprimir){
						$NumeroPagina = $NumeroPagina + 1;
                        $pdf->AddPage();
                        $pdf->SetFont('Arial','B',15);
                        $pdf->Cell(40,5,"",0,0,"L");
                        $pdf->Cell(70,5,$nombre_empresa,0,0,"L");
                        $pdf->Ln();
            
                        $pdf->SetFont('Arial','B',7);
                        $pdf->Cell(40,5,"",0,0,"L");
                        $pdf->Cell(55,5,utf8_decode("Fecha y hora de Expedición"),0,0,"L");
                        $pdf->Cell(55,5,$fecha_hora,0,0,"L");
                        $pdf->Ln();
                        $pdf->Cell(40,5,"",0,0,"L");
                        $pdf->Cell(55,5,utf8_decode("Número de Serie de Certificado del Emisor"),0,0,"L");
                        $pdf->Cell(55,5,$numcer,0,0,"L");
                        $pdf->Ln();
                        $pdf->Cell(40,5,"",0,0,"L");
                        $pdf->Cell(55,5,utf8_decode("Número de Serie de Certificado del SAT"),0,0,"L");
                        $pdf->Cell(55,5,$cerSAT,0,0,"L");
                        $pdf->Ln();
                        $pdf->Cell(40,5,"",0,0,"L");
                        $pdf->Cell(55,5,utf8_decode("Folio Fiscal"),0,0,"L");
                        $pdf->Cell(55,5,$uuid,0,0,"L");
                        $pdf->Ln();
                        $pdf->Cell(40,5,"",0,0,"L");
                        $pdf->Cell(55,5,utf8_decode("Fecha y hora de Certificación"),0,0,"L");
                        $pdf->Cell(55,5,$fechatimbre,0,0,"L");
                        $pdf->Ln();
                        $pdf->Cell(40,5,"",0,0,"L");
                        $pdf->Cell(55,5,utf8_decode("Versión del Comprobante"),0,0,"L");
                        $pdf->Cell(55,5,$version_compro[0],0,0,"L");
                        $pdf->Ln();
                        $pdf->SetFont('Arial','B',12);
                        $pdf->SetFillColor(213, 216, 220);
                        $pdf->SetDrawColor(213, 216, 220);
        				$pdf->Cell(190,1,"",1,0,"L",true);
                        $pdf->Cell(5,5,"",0,0,"L");
                        $pdf->Cell(80,5,"",0,0,"C");
                        $pdf->SetFont('Arial','B',8);
						
                        $pdf->Image($logo_empresa,10,10,35,35,$extension,'');
                        $pdf->Ln(); 
                        $pdf->Ln();
                        $pdf->Cell(15,10,"Codigo",1,0,"L",true);
                        $pdf->Cell(15,10,"ClaveProdSer",1,0,"L",true);
                        $pdf->Cell(65,10,"Descricion",1,0,"L",true);
                        $pdf->Cell(35,10,"Unidad",1,0,"L",true);
                        $pdf->Cell(25,10,"Precio",1,0,"R",true);
                        $pdf->Cell(15,10,"Cantidad",1,0,"R",true);
                        $pdf->Cell(20,10,"Importe",1,0,"R",true);
                        $pdf->Ln();
						$i=0;
						$pdf->SetFont('Arial','',6);
						$cont_h = 1;
						$y=65;
                        $contadorconceptos = 0;
						$conceptosimprimir = 20;
                    }
                    $pdf->Cell(15,5,$dato["ClaveUnidad"],1,0,"L");
                    $pdf->Cell(15,5,$dato["ClaveProdServ"],1,0,"L");
                    $pdf->MultiCell( 65, 5, $dato["Descripcion"], 1);
                    if($cont_h==1){
                        $pdf->SetXY(105, $y);
                        $cont_h++;
                    }else{
                        $y+=10;
                        $pdf->SetXY(105, $y);
                    }
                    //$pdf->Cell(65,5,$dato["Descripcion"],1,0,"L");
                    $pdf->Cell(35,5,utf8_decode($dato["Unidad"]),1,0,-($numcaracteres-67));
                    $pdf->Cell(25,5,number_format($dato['ValorUnitario']."",2,'.',','),1,0,"R");
                    $pdf->Cell(15,5,$dato['Cantidad'],1,0,"C");
                    $pdf->Cell(20,5,number_format($dato['Importe']."",2,'.',','),1,0,"R");
                    $pdf->Ln();
					$contadorconceptos = $contadorconceptos + 1;
                }
                // foreach($resp as $d){
                //     $pdf->Cell(15,5,$d['Codigo'],1,0,"L");
                //     $pdf->Cell(15,5,$d['c_ClaveProdServ'],1,0,"L");
                //     $numcaracteres=strlen(utf8_decode($d['Descripcion']));
                //     if($numcaracteres>50){
                //     $rest = substr(utf8_decode($d['Descripcion']),0,48);
                //     $rest2 = substr(utf8_decode($d['Descripcion']),48,50);
                //     $rest3 = substr(utf8_decode($d['Descripcion']),98,$numcaracteres);
                //     }else{
                //     $rest=utf8_decode($d['Descripcion']);
                //     $rest2="";
                //     $rest3 = "";
                //     }
                //     $pdf->Cell(65,5,$rest,1,0,"L");
                //     $pdf->Cell(35,5,utf8_decode($d['Unidad']),1,0,-($numcaracteres-67));
                //     $pdf->Cell(25,5,formatonum($d['ValorUnitario']),1,0,"R");
                //     $pdf->Cell(15,5,$d['Cantidad'],1,0,"C");
                //     $pdf->Cell(20,5,formatonum($d['Importe']),1,0,"R");
                //     $pdf->Ln();
                //     if($rest2 != ""){
                //         $pdf->Cell(15,5,"",1,0,"L");
                //         $pdf->Cell(15,5,"",1,0,"L");
                //         $pdf->Cell(65,5,trim($rest2),1,0,"L");
                //         $pdf->Cell(35,5,"",1,0,-($numcaracteres-67));
                //         $pdf->Cell(25,5,"",1,0,"R");
                //         $pdf->Cell(15,5,"",1,0,"C");
                //         $pdf->Cell(20,5,"",1,0,"R");
                //         $pdf->Ln();    
                //     }
                //     if($rest3 != ""){
                //     $pdf->Cell(15,5,"",1,0,"L");
                //         $pdf->Cell(15,5,"",1,0,"L");
                //         $pdf->Cell(65,5,$rest3,1,0,"L");
                //         $pdf->Cell(35,5,"",1,0,-($numcaracteres-67));
                //         $pdf->Cell(25,5,"",1,0,"R");
                //         $pdf->Cell(15,5,"",1,0,"C");
                //         $pdf->Cell(20,5,"",1,0,"R");
                //         $pdf->Ln();  
                //     }
                    
        
        
                //     $i++;
                // }
                /////
                ///FIN Obtencion de datos contables de la factura
                /////
    
                // if($datos[9]==1){
                //     $pdf->Cell(190,5,"INE",1,0,"C",true);
                //     $pdf->Ln();
                //     $pdf->Cell(40,5,"Tipo Proceso",1,0,"L",true);
                //     $pdf->Cell(45,5,"Tipo Comite",1,0,"L",true);
                //     $pdf->Cell(35,5,"Entidad",1,0,"L",true);
                //     $pdf->Cell(35,5,"Ambito",1,0,"L",true);
                //     $pdf->Cell(35,5,"ID Contabilidad",1,0,"L",true);
                //     $pdf->Ln();
    
                //     ///Imprimir resultados--------
                //     for($e=0;$e<count($claveentidad);$e++)
                //     {
                //         if($e==0){
                //         $pdf->Cell(40,5,$tipoproceso,1,0,"L");
                //         $pdf->Cell(45,5,$tipocomite,1,0,"L");
                //         }
                //         $pdf->Cell(35,5,@$claveentidad[$e],1,0,"L");
                //         if(@$ambito[$e]=="X"){
                //         $ambito[$e]="";
                //         }
                //         $pdf->Cell(35,5,@$ambito[$e],1,0,"L");
                //         $pdf->Cell(35,5,@$idcontabilidaddet[$e],1,0,"L");
            
                //         $pdf->Ln();
                //     }
                // }
                // if($importeotros>0){
                //     $pdf->Ln();
                //     $pdf->Cell(190,5,"Impuestos Estatales",1,0,"C",true);
                //     $pdf->Ln();
                //     $pdf->Cell(80,5,"Impuesto",1,0,"L",true);
                //     $pdf->Cell(30,5,"Tasa",1,0,"L",true);
                //     $pdf->Cell(80,5,"Importe",1,0,"L",true);
                //     $pdf->Ln();
                //     for($i=0;$i<count($tasaotrosimp);$i++){
                //         $pdf->Cell(80,5,"ISSVFBCA",1,0,"L");
                //         $pdf->Cell(30,5,$tasaotrosimp[$i],1,0,"L");
                //         $pdf->Cell(80,5,$importeotrosimp[$i],1,0,"L");
                //         $pdf->Ln();
                //     }
                // }
    
                ////
                //Obtencion de los valores totales de la factura
                ////
    			
				if((($NumeroPagina > 1) && ($contadorconceptos > 10)) || (($NumeroPagina == 1) && ($contadorconceptos > 5))){
                        $pdf->AddPage();
                        $pdf->SetFont('Arial','B',15);
                        $pdf->Cell(40,5,"",0,0,"L");
                        $pdf->Cell(70,5,$nombre_empresa,0,0,"L");
                        $pdf->Ln();
            
                        $pdf->SetFont('Arial','B',7);
                        $pdf->Cell(40,5,"",0,0,"L");
                        $pdf->Cell(55,5,utf8_decode("Fecha y hora de Expedición"),0,0,"L");
                        $pdf->Cell(55,5,$fecha_hora,0,0,"L");
                        $pdf->Ln();
                        $pdf->Cell(40,5,"",0,0,"L");
                        $pdf->Cell(55,5,utf8_decode("Número de Serie de Certificado del Emisor"),0,0,"L");
                        $pdf->Cell(55,5,$numcer,0,0,"L");
                        $pdf->Ln();
                        $pdf->Cell(40,5,"",0,0,"L");
                        $pdf->Cell(55,5,utf8_decode("Número de Serie de Certificado del SAT"),0,0,"L");
                        $pdf->Cell(55,5,$cerSAT,0,0,"L");
                        $pdf->Ln();
                        $pdf->Cell(40,5,"",0,0,"L");
                        $pdf->Cell(55,5,utf8_decode("Folio Fiscal"),0,0,"L");
                        $pdf->Cell(55,5,$uuid,0,0,"L");
                        $pdf->Ln();
                        $pdf->Cell(40,5,"",0,0,"L");
                        $pdf->Cell(55,5,utf8_decode("Fecha y hora de Certificación"),0,0,"L");
                        $pdf->Cell(55,5,$fechatimbre,0,0,"L");
                        $pdf->Ln();
                        $pdf->Cell(40,5,"",0,0,"L");
                        $pdf->Cell(55,5,utf8_decode("Versión del Comprobante"),0,0,"L");
                        $pdf->Cell(55,5,$version_compro[0],0,0,"L");
                        $pdf->Ln();
                        $pdf->SetFont('Arial','B',12);
                        $pdf->SetFillColor(213, 216, 220);
                        $pdf->SetDrawColor(213, 216, 220);
        				$pdf->Cell(190,1,"",1,0,"L",true);
                        $pdf->Cell(5,5,"",0,0,"L");
                        $pdf->Cell(80,5,"",0,0,"C");
                        $pdf->SetFont('Arial','B',8);
						
                        $pdf->Image($logo_empresa,10,10,35,35,$extension,'');
                        $pdf->Ln(); 
                        
						$i=0;
						$pdf->SetFont('Arial','',6);
						$cont_h = 1;
						$y=65;
                        $contadorconceptos = 0;
						$conceptosimprimir = 20;
                    }
				
    
                $pdf->Ln();
                $pdf->SetFont('Arial','',8);
                $pdf->Ln();
                $pdf->Ln();
                $pdf->Cell(190,5,"Totales",1,0,"C",true);
                $pdf->Ln();
                $pdf->Cell(40,5,"SubTotal",1,0,"R",true);
                $pdf->Cell(40,5,"Descuento",1,0,"R",true);
                $pdf->Cell(25,5,"IVA",1,0,"R",true);
                $pdf->Cell(25,5,"IEPS",1,0,"R",true);
                $pdf->Cell(25,5,"O.Impuesto",1,0,"R",true);
                $pdf->Cell(35,5,"Total",1,0,"R",true);
                $pdf->Ln();
                $pdf->Cell(40,5,$subtotal,1,0,"R");
    
                ///Imprimir resultados--------
                $pdf->Cell(40,5,$descuento,1,0,"R");
                $pdf->Cell(25,5,$iva,1,0,"R");
                $pdf->Cell(25,5,$ieps,1,0,"R");
                $pdf->Cell(25,5,$otros,1,0,"R");
                $pdf->Cell(35,5,$total,1,0,"R");
                $pdf->Ln();
    
                ////
                //FIN de Obtencion de los valores totales de la factura
                ////
    
                $pdf->Ln();
                $cont=$cont+10;
                $cont=$cont+15;
    
                ////
                //Imprimir datos en el pie de la factura
                ////
    
                $pdf->Ln();
                $pdf->Ln();
    
                $pdf->SetFont('Arial','B',6);
                $pdf->Cell(180,5,utf8_decode("Observaciones"),0,0,"L");
                $pdf->SetFont('Arial','',6);
                $pdf->Ln();
                $pdf->Cell(180,5,utf8_decode($observaciones),0,0,"L");
    
                $pdf->Ln();
                $pdf->SetFont('Arial','B',6);
                $pdf->Cell(180,5,utf8_decode("Cadena Original del Complemento de Certificación Digital del SAT"),0,0,"L");
                $pdf->SetFont('Arial','',6);
                $pdf->Ln();
                $pdf->MultiCell(190,5,utf8_decode("||1.0|".$uuid."|".$fechatimbre."|".$selloCFD."|".$numcer."||"));
    
                $pdf->SetFont('Arial','B',6);
                $pdf->Cell(180,5,utf8_decode("Sello Digital del Emisor"),0,0,"L");
    
                $pdf->SetFont('Arial','',6);
                $pdf->Ln();
                $pdf->MultiCell(190,5,utf8_decode($selloCFD));
    
                $pdf->SetFont('Arial','B',6);
                $pdf->Cell(180,5,utf8_decode("Sello Digital SAT"),0,0,"L");
    
                $pdf->SetFont('Arial','',6);
                $pdf->Ln();
                $pdf->MultiCell(190,5,utf8_decode($selloSAT));
                $pdf->Image($temp_image,170,15,30,30,'PNG','');
                //$pdf->Image($logo_empresa,10,10,35,35,$extension,'');
    
                ////
                //FIN deImprimir datos en el pie de la factura
                ////
    
    
                // unlink($folio.".png");
                // unlink($folio.".jpg");
    
                // $emp=str_pad($datos[57], 3, "0", STR_PAD_LEFT);
                // $fol=str_pad($folio, 6, "0", STR_PAD_LEFT);
                // $nom=$emp."_F".$serie.$fol;
                unlink($temp_image);
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
        ->select("gcf.nombre","gce.empresa","no_certificado","gce.rfc")
        ->leftJoin("gen_cat_fotografia as gcf","gcf.id_fotografia","=","gce.id_fotografia")
        ->where("id_empresa",$datos["id_empresa"])
        ->first();
        $cliente = DB::table('fac_catclientes')
        ->select("rfc","razon_social")
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
        $version_compro="3.3";
        // $serie = $datos["serie"];
        $folio =$datos["folio"];
        $fecha_comprobante="";
        $forma_pago=$datos["id_formapago"];
        $metodo_pago=$datos["id_metodopago"];
        $moneda=$datos["id_tipomoneda"];
        $selloCFD="";
        $total="$".$datos["total"];
        $subtotal="$".$datos["subtotal"];
        $descuento="$". $datos["descuento_t"];
        $iva="$ ".$datos["iva_t"];
        $ieps="$ ".$datos["ieps_t"];
        $otros="$".$datos["otros_t"];
        $cont=40;
        //Datos emisor
        $rfc_emisor=$xml->rfc;
        $nombre_emisor=$xml->empresa;
        $tipo_regimen="601";
        $direccion_emisor="";
        //Daos receptor
        $nombre_receptor=$cliente->razon_social;
        $rfc_receptor=$cliente->rfc;
        $direccion_receptor="";
        $uso_cfdi=$datos["id_usocfdi"];
        //carga pdf
        $pdf = new Fpdf('P','mm','A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',15);
        $pdf->Cell(40,5,"",0,0,"L");
        $pdf->Cell(70,5,$nombre_empresa,0,0,"L");
        $pdf->Ln();

        $pdf->SetFont('Arial','B',7);
        $pdf->Cell(40,5,"",0,0,"L");
        $pdf->Cell(55,5,utf8_decode("Fecha y hora de Expedición"),0,0,"L");
        $pdf->Cell(55,5,$fecha_hora,0,0,"L");
        $pdf->Ln();
        $pdf->Cell(40,5,"",0,0,"L");
        $pdf->Cell(55,5,utf8_decode("Número de Serie de Certificado del Emisor"),0,0,"L");
        $pdf->Cell(55,5,$numcer,0,0,"L");
        $pdf->Ln();
        $pdf->Cell(40,5,"",0,0,"L");
        $pdf->Cell(55,5,utf8_decode("Número de Serie de Certificado del SAT"),0,0,"L");
        $pdf->Cell(55,5,$cerSAT,0,0,"L");
        $pdf->Ln();
        $pdf->Cell(40,5,"",0,0,"L");
        $pdf->Cell(55,5,utf8_decode("Folio Fiscal"),0,0,"L");
        $pdf->Cell(55,5,$uuid,0,0,"L");
        $pdf->Ln();
        $pdf->Cell(40,5,"",0,0,"L");
        $pdf->Cell(55,5,utf8_decode("Fecha y hora de Certificación"),0,0,"L");
        $pdf->Cell(55,5,$fechatimbre,0,0,"L");
        $pdf->Ln();
        $pdf->Cell(40,5,"",0,0,"L");
        $pdf->Cell(55,5,utf8_decode("Versión del Comprobante"),0,0,"L");
        $pdf->Cell(55,5,$version_compro[0],0,0,"L");
        $pdf->Ln();
        $pdf->SetFont('Arial','B',12);
        $pdf->SetFillColor(213, 216, 220);
        $pdf->SetDrawColor(213, 216, 220);

        // $pdf->Image($logo_empresa,13,13,25,25,$extension,'');
        $pdf->Ln(); 
        
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(105,5,"Datos del Receptor",1,0,"C",true);
        $pdf->Cell(5,5,"",0,0,"L");
        $pdf->Cell(80,5,"Datos de Factura",1,0,"C",true);
        $pdf->Ln(); 
        $pdf->SetFont('Arial','',7);
        $pdf->Cell(20,5,"RFC",1,0,"L");
        $pdf->Cell(85,5,$rfc_receptor,1,0,"L");
        $pdf->Cell(5,5,"",0,0,"L");
        $pdf->Cell(25,5,"Serie y Folio",1,0,"L");
        $pdf->Cell(55,5,$serie." ".$folio,1,0,"L");
        $pdf->Ln(); 
        $pdf->Cell(20,5,"Nombre",1,0,"L");
        $pdf->Cell(85,5,$nombre_receptor,1,0,"L");
        $pdf->Cell(5,5,"",0,0,"L");
        $pdf->Cell(25,5,"Fecha",1,0,"L");
        $pdf->Cell(55,5,$fecha_comprobante,1,0,"L");
        $pdf->Ln(); 
        $pdf->Cell(20,5,utf8_decode("Dirección"),1,0,"L");

        $numcaracteres=strlen(utf8_decode($direccion_receptor));
        if($numcaracteres>58){
        $direc1 = substr(utf8_decode($direccion_receptor),0,58);
        $direc2 = substr(utf8_decode($direccion_receptor),58,57);
        $direc3 = substr(utf8_decode($direccion_receptor),115,$numcaracteres);
        }else{
        $direc1=utf8_decode($direccion_receptor);
        $direc2="";
        $direc3="";
        }

        $pdf->Cell(85,5,$direc1,1,0,"L");
        $pdf->Cell(5,5,"",0,0,"C");
        $pdf->Cell(25,5,"Cert CSD",1,0,"L");
        $pdf->Cell(55,5,$numcer,1,0,"L");
        $pdf->Ln();
        $pdf->Cell(20,5,"",1,0,"L");
        $pdf->Cell(80,5,"",0,0,"L");
        $pdf->Cell(5,5,"",0,0,"L");
        $pdf->Cell(5,5,"",0,0,"C");

        $forma_pago = DB::table('sat_FormaPago')->select("descripcion")->where("id_formapago",$forma_pago)->first();
        $pdf->Cell(25,5,utf8_decode("Forma de Pago"),1,0,"L");///////////////////////////////////////////////////////////sdfgsdfrgsdrfgsdfghsdfsdgfsdfgsdfsdf


        // $respnc = $conexion->ejecutarconsulta("SELECT NumeroCuenta From tim_encfacturas WHERE FacturaID =$FacturaID");
        // $dnc = $respnc->fetch_array(MYSQLI_ASSOC);
        $pdf->Cell(55,5,utf8_decode($forma_pago->descripcion),1,0,"L");
        $pdf->Ln();

        $pdf->Cell(20,5,"",1,0,"L");
        $pdf->Cell(85,5,"",1,0,"L");
        //$pdf->Cell(20,5,"utf8_decode("N° de Cuenta")",1,0,"L");
        //$pdf->Cell(85,5,($dnc['NumeroCuenta']==0?"":$dnc['NumeroCuenta']),1,0,"L");

        $pdf->Cell(5,5,"",0,0,"L");
        $pdf->Cell(25,5,utf8_decode("Método de Pago"),1,0,"L");///////////////////////////////////////////////////////////sdfgsdfrgsdrfgsdfghsdfsdgfsdfgsdfsdf

        $metodo_pago = DB::table('sat_MetodoPago')->select("descripcion")->where("id_metodopago",$metodo_pago)->first();
        $pdf->Cell(55,5,utf8_decode($metodo_pago->descripcion),1,0,"L");
        $pdf->Ln();
        $uso_cfdi = DB::table('sat_UsoCFDI')->select("descripcion")->where("id_usocfdi",$uso_cfdi)->first();
        $pdf->Cell(20,5,"Uso CFDI",1,0,"L");
        $pdf->Cell(85,5,utf8_decode($uso_cfdi->descripcion),1,0,"L");
        $pdf->Cell(5,5,"",0,0,"L");
        $pdf->Cell(25,5,utf8_decode("Tipo de Moneda"),1,0,"L");///////////////////////////////////////////////////////////sdfgsdfrgsdrfgsdfghsdfsdgfsdfgsdfsdf


        $tipo_moneda = DB::table('sat_CatMoneda')->select("descripcion")->where("id_catmoneda",$moneda)->first();
        $pdf->Cell(55,5,$tipo_moneda->descripcion ."(".$moneda.")",1,0,"L");
        $pdf->Ln();

        $pdf->Cell(105,5,"",0,0,"L",true);
        $pdf->Cell(5,5,"",0,0,"L");
        $pdf->Cell(80,5,"",0,0,"L",true);
        $pdf->Ln();
        $pdf->Ln();
        //$pdf->SetXY(310,10);          // Primero establece Donde estará la esquina superior izquierda donde estará tu celda 
        //$pdf->SetTextColor(255,255,255);  // Establece el color del texto (en este caso es blanco) 
        // establece el color del fondo de la celda (en este caso es AZUL 

        $pdf->SetFont('Arial','B',8);
        ///Imprimir resultados--------
        $pdf->Cell(190,5,"Datos del Emisor",1,0,"C",true);
        $pdf->SetFont('Arial','',7);
        $pdf->Ln();
        $pdf->Cell(190,5,$rfc_emisor,0,0,"C");
        $pdf->Ln();
        $pdf->Cell(190,5,$nombre_emisor,0,0,"C");
        $pdf->Ln();
        $pdf->Cell(190,5,"Expedido: ",0,0,"C");
        $pdf->Ln();
        $pdf->Cell(190,5,"Tipo Regimen: ".$tipo_regimen,0,0,"C");
        $pdf->Ln();

        $pdf->Cell(190,1,"",1,0,"L",true);
        $pdf->Cell(5,5,"",0,0,"L");
        $pdf->Cell(80,5,"",0,0,"C");
        $pdf->SetFont('Arial','B',8);

        /////
        ///Obtencion de datos contables de la factura------------------
        /////
        $pdf->Ln();
        $pdf->Cell(15,10,"Codigo",1,0,"L",true);
        $pdf->Cell(15,10,"ClaveProdSer",1,0,"L",true);
        $pdf->Cell(65,10,"Descricion",1,0,"L",true);
        $pdf->Cell(35,10,"Unidad",1,0,"L",true);
        $pdf->Cell(25,10,"Precio",1,0,"R",true);
        $pdf->Cell(15,10,"Cantidad",1,0,"R",true);
        $pdf->Cell(20,10,"Importe",1,0,"R",true);
        $pdf->Ln();


        $i=0;
        $pdf->SetFont('Arial','',6);
        foreach($datos["conceptos"] as $dato){
            $concepto = DB::table('fac_catconceptos as fc')
            ->select("ClaveProdServ")
            ->join("sat_ClaveProdServ as scp","fc.id_ClaveProdServ","=","scp.id_ClaveProdServ")
            ->where("id_concepto_empresa",$dato["id_concepto"])
            ->first();
            $pdf->Cell(15,5,"",1,0,"L");
            $pdf->Cell(15,5,$concepto->ClaveProdServ,1,0,"L");
            $pdf->Cell(65,5,$dato["descripcion"],1,0,"L");
            $pdf->Cell(35,5,utf8_decode($dato["unidad"]),1,0,-($numcaracteres-67));
            $pdf->Cell(25,5,number_format($dato['precio']."",2,'.',','),1,0,"R");
            $pdf->Cell(15,5,$dato['cantidad'],1,0,"C");
            $pdf->Cell(20,5,number_format($dato['neto']."",2,'.',','),1,0,"R");
            $pdf->Ln();
        }
        // foreach($resp as $d){
        //     $pdf->Cell(15,5,$d['Codigo'],1,0,"L");
        //     $pdf->Cell(15,5,$d['c_ClaveProdServ'],1,0,"L");
        //     $numcaracteres=strlen(utf8_decode($d['Descripcion']));
        //     if($numcaracteres>50){
        //     $rest = substr(utf8_decode($d['Descripcion']),0,48);
        //     $rest2 = substr(utf8_decode($d['Descripcion']),48,50);
        //     $rest3 = substr(utf8_decode($d['Descripcion']),98,$numcaracteres);
        //     }else{
        //     $rest=utf8_decode($d['Descripcion']);
        //     $rest2="";
        //     $rest3 = "";
        //     }
        //     $pdf->Cell(65,5,$rest,1,0,"L");
        //     $pdf->Cell(35,5,utf8_decode($d['Unidad']),1,0,-($numcaracteres-67));
        //     $pdf->Cell(25,5,formatonum($d['ValorUnitario']),1,0,"R");
        //     $pdf->Cell(15,5,$d['Cantidad'],1,0,"C");
        //     $pdf->Cell(20,5,formatonum($d['Importe']),1,0,"R");
        //     $pdf->Ln();
        //     if($rest2 != ""){
        //         $pdf->Cell(15,5,"",1,0,"L");
        //         $pdf->Cell(15,5,"",1,0,"L");
        //         $pdf->Cell(65,5,trim($rest2),1,0,"L");
        //         $pdf->Cell(35,5,"",1,0,-($numcaracteres-67));
        //         $pdf->Cell(25,5,"",1,0,"R");
        //         $pdf->Cell(15,5,"",1,0,"C");
        //         $pdf->Cell(20,5,"",1,0,"R");
        //         $pdf->Ln();    
        //     }
        //     if($rest3 != ""){
        //     $pdf->Cell(15,5,"",1,0,"L");
        //         $pdf->Cell(15,5,"",1,0,"L");
        //         $pdf->Cell(65,5,$rest3,1,0,"L");
        //         $pdf->Cell(35,5,"",1,0,-($numcaracteres-67));
        //         $pdf->Cell(25,5,"",1,0,"R");
        //         $pdf->Cell(15,5,"",1,0,"C");
        //         $pdf->Cell(20,5,"",1,0,"R");
        //         $pdf->Ln();  
        //     }
            


        //     $i++;
        // }
        /////
        ///FIN Obtencion de datos contables de la factura
        /////

        // if($datos[9]==1){
        //     $pdf->Cell(190,5,"INE",1,0,"C",true);
        //     $pdf->Ln();
        //     $pdf->Cell(40,5,"Tipo Proceso",1,0,"L",true);
        //     $pdf->Cell(45,5,"Tipo Comite",1,0,"L",true);
        //     $pdf->Cell(35,5,"Entidad",1,0,"L",true);
        //     $pdf->Cell(35,5,"Ambito",1,0,"L",true);
        //     $pdf->Cell(35,5,"ID Contabilidad",1,0,"L",true);
        //     $pdf->Ln();

        //     ///Imprimir resultados--------
        //     for($e=0;$e<count($claveentidad);$e++)
        //     {
        //         if($e==0){
        //         $pdf->Cell(40,5,$tipoproceso,1,0,"L");
        //         $pdf->Cell(45,5,$tipocomite,1,0,"L");
        //         }
        //         $pdf->Cell(35,5,@$claveentidad[$e],1,0,"L");
        //         if(@$ambito[$e]=="X"){
        //         $ambito[$e]="";
        //         }
        //         $pdf->Cell(35,5,@$ambito[$e],1,0,"L");
        //         $pdf->Cell(35,5,@$idcontabilidaddet[$e],1,0,"L");
    
        //         $pdf->Ln();
        //     }
        // }
        // if($importeotros>0){
        //     $pdf->Ln();
        //     $pdf->Cell(190,5,"Impuestos Estatales",1,0,"C",true);
        //     $pdf->Ln();
        //     $pdf->Cell(80,5,"Impuesto",1,0,"L",true);
        //     $pdf->Cell(30,5,"Tasa",1,0,"L",true);
        //     $pdf->Cell(80,5,"Importe",1,0,"L",true);
        //     $pdf->Ln();
        //     for($i=0;$i<count($tasaotrosimp);$i++){
        //         $pdf->Cell(80,5,"ISSVFBCA",1,0,"L");
        //         $pdf->Cell(30,5,$tasaotrosimp[$i],1,0,"L");
        //         $pdf->Cell(80,5,$importeotrosimp[$i],1,0,"L");
        //         $pdf->Ln();
        //     }
        // }

        ////
        //Obtencion de los valores totales de la factura
        ////


        $pdf->SetFont('Arial','',8);
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Cell(190,5,"Totales",1,0,"C",true);
        $pdf->Ln();
        $pdf->Cell(40,5,"SubTotal",1,0,"R",true);
        $pdf->Cell(40,5,"Descuento",1,0,"R",true);
        $pdf->Cell(25,5,"IVA",1,0,"R",true);
        $pdf->Cell(25,5,"IEPS",1,0,"R",true);
        $pdf->Cell(25,5,"O.Impuesto",1,0,"R",true);
        $pdf->Cell(35,5,"Total",1,0,"R",true);
        $pdf->Ln();

        $observaciones="";
        $pdf->Cell(40,5,$subtotal,1,0,"R");

        ///Imprimir resultados--------
        $pdf->Cell(40,5,$descuento,1,0,"R");
        $pdf->Cell(25,5,$iva,1,0,"R");
        $pdf->Cell(25,5,$ieps,1,0,"R");
        $pdf->Cell(25,5,$otros,1,0,"R");
        $pdf->Cell(35,5,$total,1,0,"R");
        $pdf->Ln();

        ////
        //FIN de Obtencion de los valores totales de la factura
        ////

        $pdf->Ln();
        $cont=$cont+10;
        $cont=$cont+15;

        ////
        //Imprimir datos en el pie de la factura
        ////

        $pdf->Ln();
        $pdf->Ln();

        $pdf->SetFont('Arial','B',6);
        $pdf->Cell(180,5,utf8_decode("Observaciones"),0,0,"L");
        $pdf->SetFont('Arial','',6);
        $pdf->Ln();
        $pdf->Cell(180,5,utf8_decode($observaciones),0,0,"L");

        $pdf->Ln();
        $pdf->SetFont('Arial','B',6);
        $pdf->Cell(180,5,utf8_decode("Cadena Original del Complemento de Certificación Digital del SAT"),0,0,"L");
        $pdf->SetFont('Arial','',6);
        $pdf->Ln();
        $pdf->MultiCell(190,5,utf8_decode("||1.0|".$uuid."|".$fechatimbre."|".$selloCFD."|".$numcer."||"));

        $pdf->SetFont('Arial','B',6);
        $pdf->Cell(180,5,utf8_decode("Sello Digital del Emisor"),0,0,"L");

        $pdf->SetFont('Arial','',6);
        $pdf->Ln();
        $pdf->MultiCell(190,5,utf8_decode($selloCFD));

        $pdf->SetFont('Arial','B',6);
        $pdf->Cell(180,5,utf8_decode("Sello Digital SAT"),0,0,"L");

        $pdf->SetFont('Arial','',6);
        $pdf->Ln();
        $pdf->MultiCell(190,5,utf8_decode($selloSAT));
        // $pdf->Image($folio.".jpg",170,15,30,30,'JPG','');
        $pdf->Image($logo_empresa,10,10,35,35,$extension,'');

        ////
        //FIN deImprimir datos en el pie de la factura
        ////


        // unlink($folio.".png");
        // unlink($folio.".jpg");

        // $emp=str_pad($datos[57], 3, "0", STR_PAD_LEFT);
        // $fol=str_pad($folio, 6, "0", STR_PAD_LEFT);
        // $nom=$emp."_F".$serie.$fol;
        return ["ok" => true, "data" => base64_encode($pdf->Output("S","ReporteFactura.pdf"))];
    }
    public function FunctionName(Type $var = null)
    {
        # code...
    }
}