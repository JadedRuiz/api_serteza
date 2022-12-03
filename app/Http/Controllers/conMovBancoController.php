<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\EncBanco;
use App\Models\CatBanco;
use App\Models\DetBanco;
use App\Models\ConConcepto;
use App\Models\SaldoBanco;
use App\Models\nom_cifras_nomina;

class conMovBancoController extends Controller
{
    public function __construct()
    {
        
    }

    public function AltaMovBanco(Request $res)
   {
        

        if($res["id_concepto"] == 0){
            return $this->crearRespuesta(2,"Debe capturar el ID del concepto, es un campo obligatorio",200);
        }
        if($res["id_catbanco"] == 0){
            return $this->crearRespuesta(2,"Debe capturar el ID del Banco, es obligatorio",200);
        }
        
        
        $id_estatus = $this->getEstatus("activo");
        
        try{

            $movtos = new EncBanco();
            $banco = CatBanco::find($res["id_catbanco"]);
            if (!$banco){
                return $this->crearRespuesta(2,"No encontro el Banco Seleccionado",200);
            }

            // $mes = date("m",$res["fechamovto"]);
            $detalles= $res["detalle"];

            $fecha = $this->getHoraFechaActual();

            $movtos->id_catbanco = $res["id_catbanco"];
            $movtos->id_concepto = $res["id_concepto"];
            $movtos->id_status = $id_estatus;
            $movtos->mes = $res["mes"];
            $movtos->ejercicio = $res["ejercicio"];
            $movtos->fechamovto = $res["fechamovto"];
            $movtos->documento = $res["documento"];
            $movtos->beneficiario = $res["beneficiario"];
            $movtos->descripcion = $res["descripcion"];
            $movtos->importe = $res["importe"];
            $movtos->usuario_creacion = $res["usuario"];
            $movtos->tipo_cambio = $res["tipo_cambio"];
            $movtos->fecha_creacion = $fecha;
            $movtos->save();
            $ultimo=DB::getPdo()->lastInsertId();

            $this->AgregarSaldosMovCuenta($res["id_catbanco"], $res["ejercicio"], $res["mes"], $res["id_concepto"] , $res["importe"], true);
            

            foreach ($detalles as $det){
                $detalle = new DetBanco();
                $detalle->id_encbanco = $ultimo;
                $detalle->id_movfactura = $det["id_movfactura"];
                $detalle->cuentacontable = $det["cuentacontable"];
                $detalle->descripcion = $det["descripcion"];
                $detalle->importe = $det["importe"];
                $detalle->iva = $det["iva"];
                $detalle->ieps = $det["ieps"];
                $detalle->retencion_iva = $det["retencion_iva"];
                $detalle->retencion_isr = $det["retencion_isr"];
                $detalle->id_cifras_nomina = $det["id_cifras_nomina"];
                $detalle->save();

                $ultimo_detalle=DB::getPdo()->lastInsertId();
                $importepago = $det["importe"] + $det["ieps"] + $det["iva"] - $det["retencion_iva"] - $det["retencion_isr"];
                $id_cifras_nomina = $det["id_cifras_nomina"];
                $id_movfactura = $det["id_movfactura"];

                // Valida si tiene capturado alguna factura
                if($id_movfactura > 0){
                    if($res["tipo_cambio"] > 0){
                        $importepago = round($importepago / $det["tipo_cambio"],2);
                    }
                    $this->facturas($id_movfactura,$importepago, true);
                } 

                // Valida si tiene capturado algun tipo de nomina
                if($id_cifras_nomina > 0){
                    $nomina = nom_cifras_nomina::find($id_cifras_nomina);
                    if ($nomina){
                        $nomina->pagos = $nomina->pagos + $importepago;
                        $nomina->save();
                    }
                }

            }
            
            return $this->crearRespuesta(1,"Banco guardado",200);
            
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),200);
        }   
   }

   public function InsertarMovCuenta($cuentaid, $ejercicio)
   {
        DB::insert('insert into ban_saldosbancos (id_catbanco,ejercicio,saldoinicial,ingreso1,ingreso2,ingreso3,ingreso4,ingreso5,ingreso6,ingreso7,ingreso8,ingreso9,ingreso10,ingreso11,ingreso12,egreso1,egreso2,egreso3,egreso4,egreso5,egreso6,egreso7,egreso8,egreso9,egreso10,egreso11,egreso12) values (?, ?, ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                     [$cuentaid, $ejercicio, 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0]);

        $ultimo=DB::getPdo()->lastInsertId();

        return $ultimo;
    }



    public function AgregarSaldosMovCuenta($cuentaid, $ejercicio, $mes, $conceptoid, $importe, $agregar)
    {
        $cuentas = SaldoBanco::select("id_saldobanco")
        ->where("id_catbanco",$cuentaid)
        ->where("ejercicio", $ejercicio)
        ->get();
        if(count($cuentas)==0){
            $this->InsertarMovCuenta($cuentaid, $ejercicio);
        }

        $concepto = ConConcepto::find($conceptoid);
        if($concepto){
            $tipomovto = $concepto->tipomovimiento;
           
            if($tipomovto == "I"){
                if($agregar){
                    $saldo = "saldoinicial = saldoinicial + ".$importe;
                    $campos = "ingreso".$mes." = ingreso".$mes." + ".$importe;
                }else{
                    $saldo = "saldoinicial = saldoinicial - ".$importe;
                    $campos = "ingreso".$mes." = ingreso".$mes." - ".$importe;
                }
                
            }else{
                if($agregar){
                    $saldo = "saldoinicial = saldoinicial - ".$importe;
                    $campos = "egreso".$mes." = egreso".$mes." + ".$importe;
                }else{
                    $saldo = "saldoinicial = saldoinicial + ".$importe;
                    $campos = "egreso".$mes." = egreso".$mes." - ".$importe;
                }
            }

            DB::update('update ban_saldosbancos set '.$campos.' where id_catbanco = ? and ejercicio = ?',
            [$cuentaid, $ejercicio]);

            DB::update('update ban_saldosbancos set '.$saldo.' where id_catbanco = ? and ejercicio > ?',
                    [$cuentaid, $ejercicio]);
        }   
        return true;
    }
   
    public function ModificarMovBanco(Request $res)
   {
        

        if($res["id_concepto"] == 0){
            return $this->crearRespuesta(2,"Debe capturar el ID del concepto, es un campo obligatorio",200);
        }
        if($res["id_catbanco"] == 0){
            return $this->crearRespuesta(2,"Debe capturar el ID del Banco, es obligatorio",200);
        }
        
        
        try{

            $movtos = EncBanco::find($res["id_encbanco"]);
            if (!$movtos){
                return $this->crearRespuesta(2,"No encontro el encabezado del Banco Seleccionado",200);
            }

            $banco = CatBanco::find($res["id_catbanco"]);
            if (!$banco){
                return $this->crearRespuesta(2,"No encontro el Banco Seleccionado",200);
            }

            // $mes = date("m",$res["fechamovto"]);
            

            $fecha = $this->getHoraFechaActual();
            $this->AgregarSaldosMovCuenta($movtos->id_catbanco, $movtos->ejercicio, $movtos->mes, $movtos->id_concepto , $movtos->importe, false);

            $movtos->id_catbanco = $res["id_catbanco"];
            $movtos->id_concepto = $res["id_concepto"];
            $movtos->mes = $res["mes"];
            $movtos->ejercicio = $res["ejercicio"];
            $movtos->fechamovto = $res["fechamovto"];
            $movtos->documento = $res["documento"];
            $movtos->beneficiario = $res["beneficiario"];
            $movtos->descripcion = $res["descripcion"];
            $movtos->importe = $res["importe"];
            $movtos->usuario_creacion = $res["usuario"];
            $movtos->tipo_cambio = $res["tipo_cambio"];
            $movtos->fecha_creacion = $fecha;
            $movtos->save();
            $ultimo=$res["id_encbanco"];

            $this->AgregarSaldosMovCuenta($res["id_catbanco"], $res["ejercicio"], $res["mes"], $res["id_concepto"] , $res["importe"], true);
            $detalles= $res["detalle"];
            // return $detalles;

            foreach ($detalles as $det){
                $dbanco = $det["id_detbanco"];
                if($dbanco > 0){
                    $detalle = DetBanco::find($dbanco);
                    $importepago = $detalle->importe + $detalle->ieps + $detalle->iva - $detalle->retencion_iva - $detalle->retencion_isr;    
                    if($detalle->id_movfactura > 0){
                        if($detalle->tipo_cambio > 0){
                            $importepago = round($importepago / $detalle->tipo_cambio,2);
                        }
                        $this->facturas($id_movfactura,$importepago, false);
                    }
                    if($detalle->id_cifras_nomina > 0){
                        $this->nominas($detalle->id_movfactura,$importepago, false);
                    }
                }else{
                    $detalle = new DetBanco();
                }
                
                $detalle->id_movfactura = $det["id_movfactura"];
                $detalle->cuentacontable = $det["cuentacontable"];
                $detalle->descripcion = $det["descripcion"];
                $detalle->importe = $det["importe"];
                $detalle->iva = $det["iva"];
                $detalle->ieps = $det["ieps"];
                $detalle->retencion_iva = $det["retencion_iva"];
                $detalle->retencion_isr = $det["retencion_isr"];
                $detalle->id_cifras_nomina = $det["id_cifras_nomina"];
                $detalle->save();

                
                $importepago = $det["importe"] + $det["ieps"] + $det["iva"] - $det["retencion_iva"] - $det["retencion_isr"];
                $id_cifras_nomina = $det["id_cifras_nomina"];
                $id_movfactura = $det["id_movfactura"];

                // Valida si tiene capturado alguna factura
                if($id_movfactura > 0){
                    if($res["tipo_cambio"] > 0){
                        $importepago = round($importepago / $det["tipo_cambio"],2);
                    }
                    $this->facturas($id_movfactura,$importepago, true);
                } 

                // Valida si tiene capturado algun tipo de nomina
                if($id_cifras_nomina > 0){
                    $this->nominas($id_cifras_nomina, $importe_pago, true);
                }

            }
            
            return $this->crearRespuesta(1,"Banco guardado",200);
            
        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),200);
        }   
   }

   public function facturas($factura, $importepago, $agregar)
   {
        $fact = CatBanco::find($factura);
        if ($fact){
            $importefactura = $fact->total;
            $pagos = $fact->pagos;

            if($agregar==true){            
                if (($pagos+$importepago) >= $importefactura){
                    $fact->id_status = $this->getEstatus("Pagado");
                }
                $fact->pagos = $importepago+$pagos;
            }else{
                if (($pagos-$importepago) >= $importefactura){
                    $fact->id_status = $this->getEstatus("Pagado");
                }else{
                    $fact->id_status = $this->getEstatus("activo");
                }
                $fact->pagos = $importepago-$pagos;
            }
            
            $fact->save();
        }
        return true;
   }

   public function nominas($nom, $importepago, $agregar){
        $nomina = nom_cifras_nomina::find($nom);
        if ($nomina){
            if($agregar==true){
                $nomina->pagos = $nomina->pagos + $importepago;
            }else{
                $nomina->pagos = $nomina->pagos - $importepago;
            }
            $nomina.save();
        }

        return true;
    }

    public function EstadoCuenta(Request $res)
   {
        

        if($res["id_catbanco"] == 0){
            return $this->crearRespuesta(2,"Debe capturar el ID del banco, es un campo obligatorio",200);
        }
        if($res["ejercicio"] == 0){
            return $this->crearRespuesta(2,"Debe enviar el EJERCICIO, es obligatorio",200);
        }
        if($res["mes"] == 0){
            return $this->crearRespuesta(2,"Debe enviar el MES a consultar, es obligatorio",200);
        }

        $bancoID = $res->get("id_catbanco");
        $mes = $res->get("mes");
        $ejercicio = $res->get("ejercicio");
        
        $campos = "sb.saldoinicial";
        $array = array();

        $fecha = date('Y-m-d',strtotime( $ejercicio."-".$mes."-01"));
        $fechainicial = date("d-m-Y",strtotime($fecha."- 1 days"));

        if ($mes > 1){
            for($i = 1; $i < $mes; $i++) {
                $campos = $campos."+ sb.ingreso".$i." - sb.egreso".$i;
            }
        }
        $campos = $campos." AS saldo";

        $SaldoInicial = DB::table('ban_catbancos AS b')
        ->join("ban_saldosbancos as sb","b.id_catbanco", "=", "sb.id_catbanco")
        ->select(DB::raw("0 as id_encbanco"), "b.id_catbanco",'sb.id_saldobanco', 
                 DB::raw("0 as mes"), DB::raw("0 as ejercicio"), DB::raw("now() as fechamovto"), DB::raw("0 as id_catconceptos"), DB::raw("'SALDO INICIAL' as descripcion"), 
                 DB::raw("'' as cuentacontable"), 
                 DB::raw("'' as concepto"), DB::raw("'I' as tipomovimiento"), DB::raw("0 as confactura"),DB::raw("0 as cancelaiva"),
         DB::raw($campos), 
         DB::raw("0 AS ingreso"),
         DB::raw("0 AS egreso, 0 AS importe"))
        ->where("b.id_catbanco", $bancoID)
        ->where("sb.ejercicio", $ejercicio)
        ->get();

        $query = DB::table('ban_encbancos AS m')
        ->join("ban_catbancos AS b","m.id_catbanco", "=", "b.id_catbanco")
        ->join("sat_catbancos AS s","b.id_bancosat", "=", "s.id_bancosat")
        ->join("con_catconceptos as c","m.id_concepto", "=", "c.id_concepto")
        ->select("m.id_encbanco", "m.id_catbanco",DB::raw('0 as id_saldobanco'), "m.mes", "m.ejercicio", "m.fechamovto", "m.id_concepto", "m.descripcion", DB::raw("'' as cuentacontable"), 
                 "c.concepto", "c.tipomovimiento", "c.confactura","c.cancelaiva",
         DB::raw("0 AS saldo"),
         DB::raw("(CASE WHEN c.tipomovimiento = 'I' THEN m.importe ELSE 0.00 END) AS ingreso"),
         DB::raw("(CASE WHEN c.tipomovimiento = 'E' THEN m.importe ELSE 0.00 END) AS egreso"),
         DB::raw("m.importe"))
        ->where("m.id_catbanco", $bancoID)
        ->where("m.mes", $mes)
        ->where("m.ejercicio", $ejercicio)
        ->orderBy("m.fechamovto", "ASC")
        ->get();
        $si = 0;

        $saldo = 0;
        foreach($SaldoInicial as $t){
            $saldo = $t->saldo;
            $si = $si + 1;
            $t->fechamovto = $fechainicial;
			$array[] = $t;
        }

        foreach($query as $t){
            $saldo = $saldo + $t->ingreso - $t->egreso;
            $t->saldo = $saldo;
			$array[] = $t;
        }

        if ($si == 0){
            $this->InsertarMovCuenta($bancoID,$ejercicio);
        }

        return $this->crearRespuesta(1, $array , 200);
        
   }

}
