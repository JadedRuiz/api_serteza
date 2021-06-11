<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ContabilidadController extends Controller
{
    public function __construct()
    {
        
    }
    public function getFacturasCP(Request $request){
        $id_empresa = $request["id_empresa"];
        $id_provcliente = $request["id_provcliente"];
        $id_bovedaxml = $request["id_bovedaxml"];
        $index = $request["index"];
        $pre_data = DB::table('con_movfacturas as mf')
        ->join("gen_cat_statu as s", "mf.id_status", "=", "s.id_statu")
        ->join("con_bovedaxml as bx", "bx.id_bovedaxml", "=", "mf.id_bovedaxml", "left outer")
        ->join("con_cativas as i", "i.id_cativas", "=", "mf.id_cativas")
        ->select("mf.*", "bx.uuid", "s.status", DB::raw("CONCAT(i.clave_sat, ' - ', i.tasa) AS iva"))
        ->where("mf.id_empresa", $id_empresa);
        if($request["id_provcliente"]){
            $pre_data->where('mf.id_provcliente', $id_provcliente);
        }
        if($request["id_bovedaxml"]){
            $pre_data->where('mf.id_bovedaxml', $id_bovedaxml);
        }
        $totales = $pre_data->count();
        $data = $pre_data->take(5)
                         ->skip($index)
                         ->get();
        return ["ok"=> true, "datos" => $data, "totales" => $totales];
    }
    public function guardarFacturas(Request $request){
        DB::insert('insert into con_movfacturas 
                    (id_empresa, id_bovedaxml, id_provcliente, id_status,
                    folio, fecha, metodopago, formapago, moneda, subtotal,
                    total, iva, retencion_iva, retencion_isr, id_cativas, 
                    cuentacontable, tipo_documento, ieps) values 
                    (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', 
                    [$request["id_empresa"], $request["id_bovedaxml"], $request["id_provcliente"],
                    $request["id_status"], $request["folio"], $request["fecha"],
                    $request["metodopago"], $request["formapago"], $request["moneda"], 
                    $request["subtotal"], $request["total"], $request["iva"],
                    $request["retencion_iva"], $request["retencion_isr"], $request["id_cativas"],
                    $request["cuentacontable"], $request["tipo_documento"], $request["ieps"]]);
        return ["ok"=> true, "mensaje" => "Elemento registrado"];       
    }
    public function actualizarFacturas(Request $request){
        DB::update('update con_movfacturas 
        set id_bovedaxml = ?, id_provcliente = ?, id_status = ?,
        folio = ?, fecha = ?, metodopago = ?, formapago = ?, moneda = ?, subtotal = ?,
        total = ?, iva = ?, retencion_iva = ?, retencion_isr = ?, id_cativas = ?, 
        cuentacontable = ?, tipo_documento = ?, ieps = ?
        where id_movfactura = ?', 
        [$request["id_bovedaxml"], $request["id_provcliente"],
        $request["id_status"], $request["folio"], $request["fecha"],
        $request["metodopago"], $request["formapago"], $request["moneda"], 
        $request["subtotal"], $request["total"], $request["iva"],
        $request["retencion_iva"], $request["retencion_isr"], $request["id_cativas"],
        $request["cuentacontable"], $request["tipo_documento"], $request["ieps"], $request["id_movfactura"]]);
        return ["ok"=> true, "mensaje" => "Elemento actualizado"];       
    }
    public function getCatIvas($id_empresa){
        $data = DB::table('con_cativas')
        ->select("id_cativas", "tasa", "clave_sat", DB::raw("CONCAT(clave_sat, ' - ', tasa) AS iva"))
        ->where("id_empresa", $id_empresa)
        ->get();
        return ["ok"=> true, "data" => $data];       
    }
    public function buscarClienteProveedor(Request $request){
        $clien_prov = $request["buscar"];
        $id_empresa = $request["id_empresa"];

        $data = DB::table('con_provcliente')
        ->select('id_provcliente', 'nombrecomercial')
        ->orWhere("nombrecomercial", "Like", '%'.$clien_prov.'%')
        ->orWhere("rfc", "Like", '%'.$clien_prov.'%')
        ->orWhere("rfc", "Like", '%'.$clien_prov.'%')
        ->where("id_empresa", $id_empresa)
        ->take(8)
        ->get();
        return ["ok"=> true, "data" => $data];       
    }
    public function xmlUpload(Request $request){
        $json = json_encode($request->input());
        $ojso = json_decode($json, true);
        $data = $ojso["data"];
        $usuario = $request["usuario"];
        $id_empresa = $request["empresa"];
        $id_bovedaxml = 0;
        $id_provcliente = 0;
        $mi_id_emisor = 0;
        $es_cliente = 0;
        $es_proveedor = 0;
        $tipo_documento = '';
        $mi_razon = '';
        $mi_rfc = '';
        foreach($data as $miData){
            $existe = DB::table('con_bovedaxml')
            ->select("id_bovedaxml")
            ->where("uuid", $miData['uuid'])
            ->count();
            if($existe == 0){
                DB::table('con_bovedaxml')->insert(
                    ['uuid' => $miData['uuid'], 
                    'fechatimbrado'=> $miData['fechaTimbrado'],
                    'xml'=> $miData["xml"],
                    'fecha_creacion'=>  $this->getHoraFechaActual(),
                    'usuario_creacion'=> $usuario
                    ]
                );
                $id_bovedaxml = DB::getPdo()->lastInsertId();
                $mi_id_emisor = DB::table('gen_cat_empresa')
                                    ->select("id_empresa")
                                    ->where("rfc", $miData['rfcEmisor'])
                                    ->count();
                if($mi_id_emisor > 0){
                    $mi_id_emisor = DB::table('gen_cat_empresa')
                    ->select("id_empresa")
                    ->where("rfc", $miData['rfcEmisor'])
                    ->first();
                    $mi_id_emisor = $mi_id_emisor->id_empresa;
                }else{
                    $mi_id_emisor = 0;
                }
                
                if($id_empresa == $mi_id_emisor){
                    // es cliente - ingreso insertar datos RECEPTOR
                    $es_cliente = 1;
                    $es_proveedor = 0;
                    $tipo_documento = 'I';
                    $mi_razon = $miData['razonReceptor'];
                    $mi_rfc = $miData['rfcReceptor'];
                }else{
                    // es proveedor - Egreso insertar datos Emisor
                    $es_proveedor = 1;
                    $es_cliente = 0;
                    $tipo_documento = 'E';
                    $mi_razon = $miData['razonEmisor'];
                    $mi_rfc = $miData['rfcEmisor'];
                }
                $existeRfc = DB::table('con_provcliente')
                            ->select("id_provcliente")
                            ->where("rfc", $miData['rfcEmisor'])
                            ->count();
                if($existeRfc == 0){
                    DB::table('gen_cat_direccion')->insert(
                        ['calle' => "",
                        'numero_interior'=> "",
                        'numero_exterior'=> "",
                        'cruzamiento_uno'=>  "",
                        'cruzamiento_dos'=>  "",
                        'codigo_postal'=>  0,
                        'colonia'=>  "",
                        'localidad'=>  "",
                        'municipio'=>  "",
                        'estado'=>  "",
                        'descripcion'=>  "",
                        'fecha_creacion'=> $this->getHoraFechaActual(),
                        'fecha_modificacion'=> $this->getHoraFechaActual(),
                        'usuario_creacion'=> $usuario,
                        'usuario_modificacion'=> $usuario,
                        'activo'=> true
                        ]
                    );
                    $id_direccion = DB::getPdo()->lastInsertId();
                    DB::table('con_provcliente')->insert(
                        ['id_empresa' => $id_empresa, 
                        'id_direccion'=> $id_direccion,
                        'id_status'=> 1,
                        'rfc'=>  $mi_rfc,
                        'razonsocial'=> $mi_razon,
                        'nombrecomercial'=> $mi_razon,
                        'contacto'=> "",
                        'telefono'=> "",
                        'telefono_dos'=> "",
                        'telefono_tres'=> "",
                        'correo'=> "",
                        'cuentacontable'=> "",
                        'esproveedor'=> $es_proveedor,
                        'escliente'=> $es_cliente,
                        'fecha_creacion'=> $this->getHoraFechaActual(),
                        'fecha_modificacion'=> $this->getHoraFechaActual(),
                        'usuario_creacion'=> $usuario,
                        'usuario_modificacion'=> $usuario
                        ]
                    );
                    $id_provcliente = DB::getPdo()->lastInsertId();
                }else{
                    $id_provcliente = DB::table('con_provcliente')
                    ->select('id_provcliente')
                    ->where('rfc', $miData['rfcEmisor'])
                    ->first();
                    $id_provcliente = $id_provcliente->id_provcliente;
                }
                $id_iva = DB::table('con_cativas')
                ->select('id_cativas')
                ->where('id_empresa', $id_empresa)
                ->where('clave_sat', $miData['clave_sat'])
                ->first();
                $id_iva = $id_iva->id_cativas;
                DB::table('con_movfacturas')->insert(
                    ['id_empresa' => $id_empresa, 
                    'id_bovedaxml'=> $id_bovedaxml,
                    'id_provcliente'=> $id_provcliente,
                    'id_status'=>  1,
                    'folio'=> $miData['folio'],
                    'fecha'=> $miData['fecha'],
                    'metodopago'=> $miData['metodopago'],
                    'formapago'=> $miData['formapago'],
                    'moneda'=> $miData['moneda'],
                    'subtotal'=> $miData['subtotal'],
                    'total'=> $miData['total'],
                    'iva'=> $miData['iva'],
                    'retencion_iva'=> 1,
                    'retencion_isr'=> 1,
                    'id_cativas'=> $id_iva,
                    'cuentacontable'=> "",
                    'tipo_documento'=> $tipo_documento,
                    'ieps' => $miData['ieps'],
                    'tipocambio' => $miData['tipo_cambio']
                    ]
                );
            }
        }
        return ["ok"=> true,"message"=> "xml`s insertador", "datos" => $data];
    }

}