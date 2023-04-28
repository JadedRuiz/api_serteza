<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\EncTicket;
use App\Models\DetTicket;
use App\Models\Factura;


class TicketController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    //
    public function guardarTicket(request $datos)
    {
        try{
            $busqueda = EncTicket::where("folio","=",$datos->folio);
            



        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
        
    }

    public function validarTicket(request $datos)
    {
        try {
            $folio = $datos["folio"];
            $importe = $datos["importe"];
            $mesactual = date("m");
			
            $ticket = DB::table('fac_enctickets as et')
            ->select("et.id_ticket", "et.id_empresa","et.folio", DB::raw('month(et.fecha) as mes_ticket') ,"et.importepagar","et.id_factura", "cs.status")
            ->join("gen_cat_statu as cs","cs.id_statu","et.id_estatus")
            ->where("et.folio",$folio)
            ->where("et.importepagar",$importe)
            ->get();
            if(count($ticket)>0){
                $mesticket = $ticket[0]->mes_ticket;
				
                if($mesticket == $mesactual){
                    if(strtoupper($ticket[0]->status) == "FACTURADO"){
                        return $this->crearRespuesta(2,"El Folio esta facturado",200);    
                    }else{
                        if(strtoupper($ticket[0]->status) == "CANCELADO"){
                            return $this->crearRespuesta(2,"El Folio esta cancelado",200);    
                        }else{
                            if(round($ticket[0]->importepagar,2) == round($importe,2)){
                                $resultado = array(
                                "id_empresa" => $ticket[0]->id_empresa,
                                "id_ticket" => $ticket[0]->id_ticket
                                );
                                return $this->crearRespuesta(1,$resultado,200);
                            }else{
                                return $this->crearRespuesta(2,"El Importe capturado es diferente al folio de la venta",200);        
                            }
                        }
                    }
                }else{
                    return $this->crearRespuesta(2,"El Folio capturado no pertenene al mes actual",200);    
                }
            }else{
                return $this->crearRespuesta(2,"El Folio no existe",200);
            }


        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);    
        }
    }

    public function facturarTicket(request $datos)
    {
        try{

            if(!isset($res["id_cliente"])){
                return $this->crearRespuesta(2,"Debe parr el campo del ID del Grupo",200);
            }
            $validarCliente = False;
            if(!isset($res["id_catcliente"])){
                $validarCliente = true;
                return $this->crearRespuesta(2,"El campo 'RFC' del operador es obligatorio",200);
            }else{
                
            }
            if(!isset($res["nom_operador"])){
                return $this->crearRespuesta(2,"El campo 'Nombre' del operador es obligatorio",200);
            }
            if(!isset($res["num_licencia"])){
                return $this->crearRespuesta(2,"El campo 'Número de licencia' del operador es obligatorio",200);
            }

            $busqueda = EncTicket::where("folio","=",$datos->folio);
            
            
            $resultado = array(
                "id_factura" => 100
                );
            return $this->crearRespuesta(1,$resultado,200);


        }catch(Throwable $e){
            return $this->crearRespuesta(2,"Ha ocurrido un error : " . $e->getMessage(),301);
        }
        
    }
}