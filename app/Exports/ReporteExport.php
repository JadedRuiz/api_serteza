<?php 

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Codedge\Fpdf\Fpdf\Fpdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ReporteExport {
    

    public function generarReporte($datos_vista, $nombre_reporte)
    {
        if($nombre_reporte == "AltaReport"){
            return $this->AltaReport($datos_vista);
        }
        if($nombre_reporte == "ModificacionReport"){
            return $this->ModificacionReport($datos_vista["detalle_modificacion"],$datos_vista["detalle_contratacion"]);
        }
        if($nombre_reporte == "GeneralReport"){
            return $this->GeneralReport($datos_vista);
        }
        if($nombre_reporte == "DepartamentoReport"){
            return $this->DepartamentoReport($datos_vista);
        }
    }
    public function AltaReport($datos)
    {
        $pdf = new Fpdf('P','mm','A4');
        $pdf->AddPage();
        if($datos[0]->name_foto != ""){
            $extension = strtoupper(explode(".",$datos[0]->name_foto)[1]);
            $pdf->Image(env("APP_URL")."/storage/cliente/".$datos[0]->name_foto,10,10,60,20,$extension,'');
        }else{
            $pdf->Image(env("APP_URL")."/storage/cliente/cliente_default_pdf.png",10,10,60,20,'PNG','');	
        }
        $pdf->Ln();
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(130,5,"",0,0,"R");
        $pdf->Ln();
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(130,5,"",0,0,"R");
        $pdf->Ln();
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(155,5,utf8_decode("FICHA DE CONTRATACIÓN DEL CANDIDATO"),0,0,"R");
        $pdf->Ln();
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(155,5,utf8_decode($datos[0]->cliente),0,0,"R");
        $pdf->Ln();
        $pdf->SetFillColor(139, 144, 151);
        $pdf->SetDrawColor(139, 144, 151);
        $pdf->Cell(190,.5,"",1,0,"R",true);
        $pdf->Ln(1);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(50,5,"NOMBRE DE LA EMPRESA: ",0,0,"L");
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(150,5,utf8_decode($datos[0]->empresa),0,0,"L");
        $pdf->Ln(4);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(50,5,"PUESTO: ",0,0,"L");
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(90,5,utf8_decode($datos[0]->puesto),0,0,"L");
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(50,5,utf8_decode("FECHA IMPRECIÓN ").date('d/m/Y'),0,1,"R");
        $pdf->Ln(.3);
        $pdf->SetFont('Arial','B',12);
        $pdf->SetFillColor(139, 144, 151);
        $pdf->SetDrawColor(139, 144, 151);
        $pdf->SetTextColor(255 ,255,255);
        $pdf->Cell(190,.5,"",1,0,"C",true);
        $pdf->Ln(6);
        $pdf->SetFont('Arial','B',8);
        //Pintar apartado del empleado
        $pdf->Cell(60,4,"DATOS GENERALES",1,0,"C",true);
        $pdf->Cell(30,4,"",0,0,"C");
        $pdf->SetFont('Arial','',8);
        $pdf->SetTextColor(0 ,0,0);
        if ($datos[0]->fotografia != "") {
            $extension = strtoupper(explode(".",$datos[0]->fotografia)[1]);
            $pdf->Image(env("APP_URL")."/storage/candidato/".$datos[0]->fotografia,150,50,50,60,$extension,'');
        }else{
            $pdf->Image(env("APP_URL")."/storage/candidato/default_user.jpg",150,50,50,60,'JPG','');	
        }
        $pdf->Ln();
        $pdf->SetTextColor(0 ,0,0);
        $pdf->Cell(35,6,"NOMBRE: ",1,0,"L");
        $pdf->Cell(105,6,$datos[0]->nombre,1,0,"L");
        $pdf->Ln();
        $pdf->Cell(35,6,"C.U.R.P: ",1,0,"L");
        $pdf->Cell(105,6,$datos[0]->curp,1,0,"L");
        $pdf->Ln();
        $pdf->Cell(35,6,"R.F.C ",1,0,"L");
        $pdf->Cell(105,6,utf8_decode($datos[0]->rfc),1,0,"L");
        $pdf->Ln();
        $pdf->Cell(35,6,"FECHA DE NACIMIENTO",1,0,"L");
        $pdf->Cell(105,6,date('d/m/Y',strtotime($datos[0]->fecha_nacimiento)),1,0,"L");
        $pdf->Ln();
        $pdf->Cell(35,6,utf8_decode("DIRECCIÓN"),1,0,"L");
        $pdf->Cell(12,6,"CALLE",1,0,"L");
        $pdf->Cell(50,6,$datos[0]->calle,1,0,"L");
        $pdf->Cell(10,6,"No.",1,0,"L");
        $pdf->Cell(12,6,$datos[0]->numero_exterior,1,0,"L");
        $pdf->Cell(10,6,"No.Int",1,0,"L");
        $pdf->Cell(11,6,$datos[0]->numero_interior,1,0,"L");

        $pdf->Ln();
        $pdf->Cell(35,6,"CRUZAMIENTOS",1,0,"L");
        $pdf->Cell(45,6,$datos[0]->cruzamiento_uno,1,0,"L");
        $pdf->Cell(15,6,"Y",1,0,"C");
        $pdf->Cell(45,6,$datos[0]->cruzamiento_dos,1,0,"L");
        $pdf->Ln();
        $pdf->Cell(35,6,"COLONIA",1,0,"L");
        $pdf->Cell(105,6,$datos[0]->colonia,1,0,"L");
        $pdf->Ln();
        $pdf->Cell(35,6,"ESTADO ",1,0,"L");
        $pdf->SetFont('Arial','B',6);
        $pdf->Cell(105,6,utf8_decode($datos[0]->estado),1,0,"L");
        $pdf->SetFont('Arial','',8);
        $pdf->Ln();
        $pdf->Cell(35,6,utf8_decode("TELEFONO"),1,0,"L");
        if($datos[0]->telefono_dos != ""){
            $pdf->Cell(105,6,$datos[0]->telefono. ' / '. $datos[0]->telefono_dos,1,0,"L");
        }else{
            $pdf->Cell(105,6,$datos[0]->telefono,1,0,"L");
        }
        $pdf->Ln();
        $pdf->Cell(35,6,utf8_decode("CORREO"),1,0,"L");
        $pdf->Cell(105,6,$datos[0]->correo,1,0,"L");
        $pdf->Ln();
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(70,6,utf8_decode("NÚMERO DE SEGURO SOCIAL"),1,0,"L");
        $pdf->Cell(70,6,$datos[0]->numero_seguro,"B",0,"R");
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(30,6,"EDAD:",1,0,"L");
        $pdf->Cell(20,6,number_format($datos[0]->edad, 2, '.', ''),1,0,"C");
        $pdf->Ln();
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(40,6,utf8_decode("TIPO SEGURO SOCIAL"),1,0,"L");
        $pdf->Cell(15,6,"IMSS ","B",0,"R");
        $pdf->SetFont('Arial','',15);
        $pdf->Cell(10,6,"O","B",0,"C");
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(15,6,"ISSTE ",'B',0,"R");
        $pdf->SetFont('Arial','',15);
        $pdf->Cell(10,6,"O",'B',0,"C");
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(15,6,"POPULAR ",'B',0,"R");
        $pdf->SetFont('Arial','',15);
        $pdf->Cell(10,6,"O",'B',0,"C");
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(15,6,"OTRO: ",'B',0,"R");
        $pdf->SetFont('Arial','',15);
        $pdf->Cell(60,6,"",1,0,"C");
        $pdf->SetFont('Arial','B',8);
        $pdf->Ln(10);

        $pdf->SetFont('Arial','B',8);
        $pdf->SetFillColor(139, 144, 151);
        $pdf->SetDrawColor(139, 144, 151);
        $pdf->SetTextColor(255 ,255,255);
        $pdf->Cell(60,4,utf8_decode("DATOS DE CONTRATACIÓN"),1,0,"C",true);
        $pdf->Ln();
        $pdf->SetFont('Arial','',8);
        $pdf->SetTextColor(0 ,0,0);
        $pdf->Cell(40,6,"EMPRESA",1,0,"L");
        if(strlen($datos[0]->empresa) > 25){
            $datos[0]->empresa = substr($datos[0]->empresa,0,-4)."...";
        }
        $pdf->Cell(65,6,$datos[0]->empresa,1,0,"L");
        $pdf->Cell(30,6,"FECHA INGRESO",1,0,"L");
        $pdf->Cell(55,6,date("d/m/Y",strtotime($datos[0]->fecha_detalle)),1,0,"L");
        $pdf->Ln();
        $pdf->Cell(40,7,"DEPARTAMENTO  ",1,0,"L");
        $pdf->Cell(55,7,$datos[0]->departamento,1,0,"L");
        $pdf->Cell(40,7,"PUESTO ",1,0,"L");
        $pdf->Cell(55,7,$datos[0]->puesto,1,0,"L");
        $pdf->Ln();
        $pdf->Cell(40,7,"SUCURSAL ",1,0,"L");
        $pdf->Cell(55,7,$datos[0]->sucursal,1,0,"L");
        $pdf->Cell(40,7,utf8_decode("NÓMINA "),1,0,"L");
        $pdf->Cell(55,7,$datos[0]->nomina,1,0,"L");
        $pdf->Ln();
        $pdf->Cell(40,6,"SUELDO DIARIO",1,0,"L");
        $pdf->Cell(55,6,"$ " . number_format($datos[0]->sueldo,2,".",","),1,0,"L");
        $pdf->Cell(40,6,"SUELDO INTEGRADO",1,0,"L");
        $pdf->Cell(55,6,"$ " . number_format($datos[0]->sueldo_neto,2,".",","),1,0,"L");
        $pdf->Ln();
        $pdf->Cell(40,7,"OBSERVACIONES ",1,0,"L");
        $pdf->Cell(150,7,utf8_decode($datos[0]->observacion),1,0,"L");
        $pdf->Ln(10);

        $pdf->SetTextColor(255 ,255,255);
        $pdf->Cell(63,5,"CANDIDATO",1,0,"C",true);
        $pdf->Cell(63,5,"RECLUTADOR",1,0,"C",true);
        $pdf->Cell(63,5,"RECURSOS HUMANOS",1,0,"C",true);
        $pdf->Ln();
        $pdf->Cell(63,20,"",1,0,"C");
        $pdf->Cell(63,20,"",1,0,"C");
        $pdf->Cell(63,20,"",1,0,"C");
        $pdf->Ln();
        $pdf->SetTextColor(255 ,255,255);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(63,5,"FIRMA",1,0,"C",true);
        $pdf->Cell(63,5,"FIRMA",1,0,"C",true);
        $pdf->Cell(63,5,"FIRMA",1,0,"C",true);
        $pdf->Ln();
        $pdf->SetFont('Arial','',8);
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell(63,5,$datos[0]->nombre,1,0,"C");
        $pdf->Cell(63,5,$datos[0]->usuario,1,0,"C");
        $pdf->Cell(63,5,"",1,0,"C");
        $pdf->Ln(1);    
        return base64_encode($pdf->Output("S","ReporteCandidato.pdf"));
    }
    public function ModificacionReport($datos, $contratacion)
    {
        $pdf = new Fpdf('P','mm','A4');
        $pdf->AddPage();
        if($datos->name_foto != ""){
            $extension = strtoupper(explode(".",$datos->name_foto)[1]);
            $pdf->Image(env("APP_URL")."/storage/cliente/".$datos->name_foto,10,10,60,20,$extension,'');
        }else{
            $pdf->Image(env("APP_URL")."/storage/cliente/cliente_default_pdf.png",10,10,60,20,'PNG','');	
        }
        $pdf->Ln();
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(130,5,"",0,0,"R");
        $pdf->Ln();
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(130,5,"",0,0,"R");
        $pdf->Ln();
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(155,5,utf8_decode("FICHA DE CONTRATACIÓN DEL CANDIDATO"),0,0,"R");
        $pdf->Ln();
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(155,5,utf8_decode($datos->cliente),0,0,"R");
        $pdf->Ln();
        $pdf->SetFillColor(139, 144, 151);
        $pdf->SetDrawColor(139, 144, 151);
        $pdf->Cell(190,.5,"",1,0,"R",true);
        $pdf->Ln(1);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(50,5,"NOMBRE DE LA EMPRESA: ",0,0,"L");
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(150,5,utf8_decode($datos->empresa),0,0,"L");
        $pdf->Ln(4);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(50,5,"PUESTO: ",0,0,"L");
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(90,5,utf8_decode($datos->puesto),0,0,"L");
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(50,5,utf8_decode("FECHA IMPRECIÓN ").date('d/m/Y'),0,1,"R");
        $pdf->Ln(.3);
        $pdf->SetFont('Arial','B',12);
        $pdf->SetFillColor(139, 144, 151);
        $pdf->SetDrawColor(139, 144, 151);
        $pdf->SetTextColor(255 ,255,255);
        $pdf->Cell(190,.5,"",1,0,"C",true);
        $pdf->Ln(6);
        $pdf->SetFont('Arial','B',8);
        //Pintar apartado del empleado
        $pdf->Cell(60,4,"DATOS GENERALES",1,0,"C",true);
        $pdf->Cell(30,4,"",0,0,"C");
        $pdf->SetFont('Arial','',8);
        $pdf->SetTextColor(0 ,0,0);
        if ($datos->fotografia != "") {
            $extension = strtoupper(explode(".",$datos->fotografia)[1]);
            $pdf->Image(env("APP_URL")."/storage/candidato/".$datos->fotografia,150,50,50,60,$extension,'');
        }else{
            $pdf->Image(env("APP_URL")."/storage/candidato/default_user.jpg",150,50,50,60,'JPG','');	
        }
        $pdf->Ln();
        $pdf->SetTextColor(0 ,0,0);
        $pdf->Cell(35,6,"NOMBRE: ",1,0,"L");
        $pdf->Cell(105,6,$datos->nombre,1,0,"L");
        $pdf->Ln();
        $pdf->Cell(35,6,"C.U.R.P: ",1,0,"L");
        $pdf->Cell(105,6,$datos->curp,1,0,"L");
        $pdf->Ln();
        $pdf->Cell(35,6,"R.F.C ",1,0,"L");
        $pdf->Cell(105,6,utf8_decode($datos->rfc),1,0,"L");
        $pdf->Ln();
        $pdf->Cell(35,6,"FECHA DE NACIMIENTO",1,0,"L");
        $pdf->Cell(105,6,date('d/m/Y',strtotime($datos->fecha_nacimiento)),1,0,"L");
        $pdf->Ln();
        $pdf->Cell(35,6,utf8_decode("DIRECCIÓN"),1,0,"L");
        $pdf->Cell(12,6,"CALLE",1,0,"L");
        $pdf->Cell(50,6,$datos->calle,1,0,"L");
        $pdf->Cell(10,6,"No.",1,0,"L");
        $pdf->Cell(12,6,$datos->numero_exterior,1,0,"L");
        $pdf->Cell(10,6,"No.Int",1,0,"L");
        $pdf->Cell(11,6,$datos->numero_interior,1,0,"L");

        $pdf->Ln();
        $pdf->Cell(35,6,"CRUZAMIENTOS",1,0,"L");
        $pdf->Cell(45,6,$datos->cruzamiento_uno,1,0,"L");
        $pdf->Cell(15,6,"Y",1,0,"C");
        $pdf->Cell(45,6,$datos->cruzamiento_dos,1,0,"L");
        $pdf->Ln();
        $pdf->Cell(35,6,"COLONIA",1,0,"L");
        $pdf->Cell(105,6,$datos->colonia,1,0,"L");
        $pdf->Ln();
        $pdf->Cell(35,6,"ESTADO ",1,0,"L");
        $pdf->SetFont('Arial','B',6);
        $pdf->Cell(105,6,utf8_decode($datos->estado),1,0,"L");
        $pdf->SetFont('Arial','',8);
        $pdf->Ln();
        $pdf->Cell(35,6,utf8_decode("TELEFONO"),1,0,"L");
        if($datos->telefono_dos != ""){
            $pdf->Cell(105,6,$datos->telefono. ' / '. $datos->telefono_dos,1,0,"L");
        }else{
            $pdf->Cell(105,6,$datos->telefono,1,0,"L");
        }
        $pdf->Ln();
        $pdf->Cell(35,6,utf8_decode("CORREO"),1,0,"L");
        $pdf->Cell(105,6,$datos->correo,1,0,"L");
        $pdf->Ln();
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(70,6,utf8_decode("NÚMERO DE SEGURO SOCIAL"),1,0,"L");
        $pdf->Cell(70,6,$datos->numero_seguro,"B",0,"R");
        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(30,6,"EDAD:",1,0,"L");
        $pdf->Cell(20,6,number_format($datos->edad, 2, '.', ''),1,0,"C");
        $pdf->Ln();
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(40,6,utf8_decode("TIPO SEGURO SOCIAL"),1,0,"L");
        $pdf->Cell(15,6,"IMSS ","B",0,"R");
        $pdf->SetFont('Arial','',15);
        $pdf->Cell(10,6,"O","B",0,"C");
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(15,6,"ISSTE ",'B',0,"R");
        $pdf->SetFont('Arial','',15);
        $pdf->Cell(10,6,"O",'B',0,"C");
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(15,6,"POPULAR ",'B',0,"R");
        $pdf->SetFont('Arial','',15);
        $pdf->Cell(10,6,"O",'B',0,"C");
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(15,6,"OTRO: ",'B',0,"R");
        $pdf->SetFont('Arial','',15);
        $pdf->Cell(60,6,"",1,0,"C");
        $pdf->SetFont('Arial','B',8);
        $pdf->Ln(10);

        $pdf->SetFont('Arial','B',8);
        $pdf->SetFillColor(139, 144, 151);
        $pdf->SetDrawColor(139, 144, 151);
        $pdf->SetTextColor(255 ,255,255);
        $pdf->Cell(60,4,utf8_decode("DATOS DE CONTRATACIÓN"),1,0,"C",true);
        $pdf->Ln();
        $pdf->SetFont('Arial','',8);
        $pdf->SetTextColor(0 ,0,0);
        $pdf->Cell(40,6,"EMPRESA",1,0,"L");
        if(strlen($contratacion->empresa) > 25){
            $contratacion->empresa = substr($contratacion->empresa,0,-4)."...";
        }
        $pdf->Cell(65,6,$contratacion->empresa,1,0,"L");
        $pdf->Cell(30,6,"FECHA INGRESO",1,0,"L");
        $pdf->Cell(55,6,date("d/m/Y",strtotime($contratacion->fecha_ingreso)),1,0,"L");
        $pdf->Ln();
        $pdf->Cell(40,7,"DEPARTAMENTO  ",1,0,"L");
        $pdf->Cell(55,7,$contratacion->departamento,1,0,"L");
        $pdf->Cell(40,7,"PUESTO ",1,0,"L");
        $pdf->Cell(55,7,$contratacion->puesto,1,0,"L");
        $pdf->Ln();
        $pdf->Cell(40,7,"SUCURSAL ",1,0,"L");
        $pdf->Cell(55,7,$contratacion->sucursal,1,0,"L");
        $pdf->Cell(40,7,utf8_decode("NÓMINA "),1,0,"L");
        $pdf->Cell(55,7,$contratacion->nomina,1,0,"L");
        $pdf->Ln();
        $pdf->Cell(40,6,"SUELDO DIARIO",1,0,"L");
        $pdf->Cell(55,6,"$ " . number_format($contratacion->sueldo_diario,2,".",","),1,0,"L");
        $pdf->Cell(40,6,"SUELDO INTEGRADO",1,0,"L");
        $pdf->Cell(55,6,"$ " . number_format($contratacion->sueldo_integrado,2,".",","),1,0,"L");
        $pdf->Ln();
        $pdf->Cell(40,7,"OBSERVACIONES ",1,0,"L");
        $pdf->Cell(150,7,utf8_decode($contratacion->descripcion),1,0,"L");
        $pdf->Ln(10);

        $pdf->SetFont('Arial','B',8);
        $pdf->SetFillColor(139, 144, 151);
        $pdf->SetDrawColor(139, 144, 151);
        $pdf->SetTextColor(255 ,255,255);
        $pdf->Cell(60,4,utf8_decode("DATOS DE MODIFICACIÓN"),1,0,"C",true);
        $pdf->Ln();
        $pdf->SetFont('Arial','',8);
        $pdf->SetTextColor(0 ,0,0);
        $pdf->Cell(40,6,"EMPRESA",1,0,"L");
        if(strlen($datos->empresa) > 25){
            $datos->empresa = substr($datos->empresa,0,-4)."...";
        }
        $pdf->Cell(65,6,$datos->empresa,1,0,"L");
        $pdf->Cell(30,6,"FECHA INGRESO",1,0,"L");
        $pdf->Cell(55,6,date("d/m/Y",strtotime($datos->fecha_detalle)),1,0,"L");
        $pdf->Ln();
        $pdf->Cell(40,7,"DEPARTAMENTO  ",1,0,"L");
        $pdf->Cell(55,7,$datos->departamento,1,0,"L");
        $pdf->Cell(40,7,"PUESTO ",1,0,"L");
        $pdf->Cell(55,7,$datos->puesto,1,0,"L");
        $pdf->Ln();
        $pdf->Cell(40,7,"SUCURSAL ",1,0,"L");
        $pdf->Cell(55,7,$datos->sucursal,1,0,"L");
        $pdf->Cell(40,7,utf8_decode("NÓMINA "),1,0,"L");
        $pdf->Cell(55,7,$datos->nomina,1,0,"L");
        $pdf->Ln();
        $pdf->Cell(40,6,"SUELDO DIARIO",1,0,"L");
        $pdf->Cell(55,6,"$ " . number_format($datos->sueldo,2,".",","),1,0,"L");
        $pdf->Cell(40,6,"SUELDO INTEGRADO",1,0,"L");
        $pdf->Cell(55,6,"$ " . number_format($datos->sueldo_neto,2,".",","),1,0,"L");
        $pdf->Ln();
        $pdf->Cell(40,7,"OBSERVACIONES ",1,0,"L");
        $pdf->Cell(150,7,utf8_decode($datos->observacion),1,0,"L");
        $pdf->Ln(10);

        $pdf->SetTextColor(255 ,255,255);
        $pdf->Cell(63,5,"CANDIDATO",1,0,"C",true);
        $pdf->Cell(63,5,"RECLUTADOR",1,0,"C",true);
        $pdf->Cell(63,5,"RECURSOS HUMANOS",1,0,"C",true);
        $pdf->Ln();
        $pdf->Cell(63,20,"",1,0,"C");
        $pdf->Cell(63,20,"",1,0,"C");
        $pdf->Cell(63,20,"",1,0,"C");
        $pdf->Ln();
        $pdf->SetTextColor(255 ,255,255);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(63,5,"FIRMA",1,0,"C",true);
        $pdf->Cell(63,5,"FIRMA",1,0,"C",true);
        $pdf->Cell(63,5,"FIRMA",1,0,"C",true);
        $pdf->Ln();
        $pdf->SetFont('Arial','',8);
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell(63,5,$datos->nombre,1,0,"C");
        $pdf->Cell(63,5,$datos->usuario,1,0,"C");
        $pdf->Cell(63,5,"",1,0,"C");
        $pdf->Ln(1);    
        return base64_encode($pdf->Output("S","ReporteCandidato.pdf"));
    }
    public function DepartamentoReport($datos)
    {
        $pdf = new Fpdf('P','mm','A4');
        $pdf->AddPage();
        if($datos[0]->name_foto != ""){
            $extension = strtoupper(explode(".",$datos[0]->name_foto)[1]);
            $pdf->Image(env("APP_URL")."/storage/cliente/".$datos[0]->name_foto,10,10,60,20,$extension,'');
        }else{
            $pdf->Image(env("APP_URL")."/storage/cliente/cliente_default_pdf.png",10,10,60,20,'PNG','');	
        }
        $pdf->Ln();
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(130,5,"",0,0,"R");
        $pdf->Ln();
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(130,5,"",0,0,"R");
        $pdf->Ln();
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(155,5,utf8_decode("FICHA DE DEPARTAMENTOS"),0,0,"R");
        $pdf->Ln();
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(155,5,utf8_decode($datos[0]->cliente),0,0,"R");
        $pdf->Ln();
        $pdf->SetFillColor(139, 144, 151);
        $pdf->SetDrawColor(139, 144, 151);
        $pdf->Cell(190,.5,"",1,0,"R",true);
        $pdf->Ln(1);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(50,5,"EMPRESA: ",0,0,"L");
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(150,5,utf8_decode($datos[0]->empresa),0,0,"L");
        $pdf->Ln(4);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(50,5,"NO. DEPARTAMENTOS: ",0,0,"L");
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(90,5,count($datos),0,0,"L");
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(50,5,utf8_decode("FECHA IMPRECIÓN ").date('d/m/Y'),0,1,"R");
        $pdf->Ln(.3);
        $pdf->SetFont('Arial','B',12);
        $pdf->SetFillColor(139, 144, 151);
        $pdf->SetDrawColor(139, 144, 151);
        $pdf->SetTextColor(255 ,255,255);
        $pdf->Cell(190,.5,"",1,0,"C",true);
        $pdf->Ln(6);

        foreach($datos as $dato){
            $pdf->SetTextColor(255 ,255,255);
            $pdf->SetFont('Arial','B',10);
            $pdf->Cell(130,10,$dato->departamento,1,0,"L",true);
            $pdf->Cell(60,10,utf8_decode("VACANTES: ".$dato->vacantes),1,0,"L",true);
            $pdf->Ln(12);
            $pdf->Cell(50,5,"PUESTO",1,0,"C",true);
            $pdf->Cell(30,5,"AUTORIZADOS",1,0,"C",true);
            $pdf->Cell(30,5,"CONTRATADOS",1,0,"C",true);
            $pdf->Cell(80,5,"DESCRIPCION",1,0,"C",true);
            $pdf->Ln();
            foreach($dato->puestos as $puesto){
                $pdf->SetTextColor(0,0,0);
                $pdf->SetFont('Arial','',7);
                $pdf->Cell(50,10,$puesto->puesto,1,0,"L");
                $pdf->Cell(30,10,$puesto->autorizados,1,0,"C");
                $pdf->SetFont('Arial','',7);
                $pdf->Cell(30,10,$puesto->contratados,1,0,"C");
                $pdf->SetFont('Arial','',7);
                $pdf->Cell(80,10,$puesto->descripcion,1,0,"L");
                $pdf->Ln();
            }
            $pdf->Ln(12);
        }
        return base64_encode($pdf->Output("S","ReporteEquipo.pdf"));
    }
    public function GeneralReport($datos)
    {
        $pdf = new Fpdf('P','mm','A4');
        $pdf->AddPage();
        if($datos[0]->foto_cliente != ""){
            $extension = strtoupper(explode(".",$datos[0]->foto_cliente)[1]);
            $pdf->Image(env("APP_URL")."/storage/cliente/".$datos[0]->foto_cliente,10,10,60,20,$extension,'');
        }else{
            $pdf->Image(env("APP_URL")."/storage/cliente/cliente_default_pdf.png",10,10,60,20,'PNG','');	
        }
        $pdf->Ln();
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(130,5,"",0,0,"R");
        $pdf->Ln();
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(130,5,"",0,0,"R");
        $pdf->Ln();
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(35,5,"",0,0,"L");
        if($datos[0]->tipo_movimiento == "A"){
            $pdf->Cell(155,5,utf8_decode("FICHA DE CONTRATACIÓNES"),0,0,"R");
        }
        if($datos[0]->tipo_movimiento == "M"){
            $pdf->Cell(155,5,utf8_decode("FICHA DE MODIFICACIÓNES"),0,0,"R");
        }
        if($datos[0]->tipo_movimiento == "B"){
            $pdf->Cell(155,5,utf8_decode("FICHA DE BAJAS"),0,0,"R");
        }
        $pdf->Ln();
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(35,5,"",0,0,"L");
        $pdf->Cell(155,5,utf8_decode($datos[0]->cliente),0,0,"R");
        $pdf->Ln();
        $pdf->SetFillColor(139, 144, 151);
        $pdf->SetDrawColor(139, 144, 151);
        $pdf->Cell(190,.5,"",1,0,"R",true);
        $pdf->Ln(1);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(50,5,utf8_decode("USUARIO CREACIÓN : "),0,0,"L");
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(150,5,utf8_decode($datos[0]->usuario),0,0,"L");
        $pdf->Ln(4);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(50,5,"NO. CONTRATACIONES : ",0,0,"L");
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(90,5,count($datos[0]->detalle),0,0,"L");
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(50,5,utf8_decode("FECHA IMPRECIÓN ").date('d/m/Y'),0,1,"R");
        $pdf->Ln(.3);
        $pdf->SetFont('Arial','B',12);
        $pdf->SetFillColor(139, 144, 151);
        $pdf->SetDrawColor(139, 144, 151);
        $pdf->SetTextColor(255 ,255,255);
        $pdf->Cell(190,.5,"",1,0,"C",true);
        $pdf->Ln(6);
        foreach($datos[0]->detalle as $detalle){
            $pdf->SetFont('Arial','B',8);
            $pdf->SetFillColor(139, 144, 151);
            $pdf->SetDrawColor(139, 144, 151);
            $pdf->SetTextColor(255 ,255,255);
            $pdf->Cell(60,4,utf8_decode($detalle->nombre.' '.$detalle->apellido_paterno.' '.$detalle->apellido_materno),1,0,"L",true);
            $pdf->Ln();
            $pdf->SetFont('Arial','',8);
            $pdf->SetTextColor(0 ,0,0);
            $pdf->Cell(40,6,"EMPRESA",1,0,"L");
            if(strlen($detalle->empresa) > 25){
                $detalle->empresa = substr($detalle->empresa,0,-4)."...";
            }
            $pdf->Cell(65,6,$detalle->empresa,1,0,"L");
            $pdf->Cell(30,6,"FECHA INGRESO",1,0,"L");
            $pdf->Cell(55,6,date("d/m/Y",strtotime($detalle->fecha_detalle)),1,0,"L");
            $pdf->Ln();
            $pdf->Cell(40,7,"DEPARTAMENTO  ",1,0,"L");
            $pdf->Cell(55,7,$detalle->departamento,1,0,"L");
            $pdf->Cell(40,7,"PUESTO ",1,0,"L");
            $pdf->Cell(55,7,$detalle->puesto,1,0,"L");
            $pdf->Ln();
            $pdf->Cell(40,7,"SUCURSAL ",1,0,"L");
            $pdf->Cell(55,7,$detalle->sucursal,1,0,"L");
            $pdf->Cell(40,7,utf8_decode("NÓMINA "),1,0,"L");
            $pdf->Cell(55,7,$detalle->nomina,1,0,"L");
            $pdf->Ln();
            $pdf->Cell(40,6,"SUELDO DIARIO",1,0,"L");
            $pdf->Cell(55,6,"$ " . number_format($detalle->sueldo,2,".",","),1,0,"L");
            $pdf->Cell(40,6,"SUELDO INTEGRADO",1,0,"L");
            $pdf->Cell(55,6,"$ " . number_format($detalle->sueldo_neto,2,".",","),1,0,"L");
            $pdf->Ln();
            $pdf->Cell(40,7,"OBSERVACIONES ",1,0,"L");
            $pdf->Cell(150,7,utf8_decode($detalle->observacion),1,0,"L");
            $pdf->Ln(10);
        }
        $pdf->SetTextColor(255 ,255,255);
        $pdf->Cell(95,5,"RECLUTADOR",1,0,"C",true);
        $pdf->Cell(95,5,"RECURSOS HUMANOS",1,0,"C",true);
        $pdf->Ln();
        $pdf->Cell(95,20,"",1,0,"C");
        $pdf->Cell(95,20,"",1,0,"C");
        $pdf->Ln();
        $pdf->SetTextColor(255 ,255,255);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(95,5,"FIRMA",1,0,"C",true);
        $pdf->Cell(95,5,"FIRMA",1,0,"C",true);
        $pdf->Ln();
        $pdf->SetFont('Arial','',8);
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell(95,5,$datos[0]->usuario,1,0,"C");
        $pdf->Cell(95,5,"",1,0,"C");
        $pdf->Ln(1); 
        return base64_encode($pdf->Output("S","ReporteContratos.pdf"));
    }
}