<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UsuarioController extends Controller
{
    public function __construct()
    {
        
    }
    public function getRegistros(){
        return count(Usuario::all());
    }
    public function obtenerUsuarios(Request $res){
        $take = $res["taken"];
        $pagina = $res["pagina"];
        $status = $res["status"];
        $palabra = $res["palabra"];
        $otro = "";
        if($status == "2"){
            $otro = "!=";
            $status = 2;
        }
        if($status == "1"){
            $status = 1;
            $otro = "=";
        }
        if($status == "0"){
            $status = 0;
            $otro = "=";
        }
        if($palabra == ""){
            $otro_dos = "!=";
            $palabra = "";
        }else{
            $otro_dos = "like";
            $palabra = "%".$palabra."%";
        }
        $incia = intval($pagina) * intval($take);
        $usuario_super_admin = DB::table('liga_usuario_sistema as lus')
        ->join("gen_cat_usuario as cu","lus.id_usuario","=","lus.id_usuario")
        ->where("id_sistema",5)
        ->first();
        $registros = DB::table('gen_cat_usuario')
        ->where("activo",$otro,$status)
        ->where("usuario",$otro_dos,$palabra)
        ->where("id_usuario","!=",$usuario_super_admin->id_usuario)
        ->skip($incia)
        ->take($take)
        ->get();
        $contar = DB::table('gen_cat_usuario')
        ->where("activo",$otro,$status)
        ->where("usuario",$otro_dos,$palabra)
        ->where("id_usuario","!=",$usuario_super_admin->id_usuario)
        ->get();
        if(count($registros)>0){
            $respuesta = [
                "total" => count($contar),
                "registros" => $registros
            ];
            return $this->crearRespuesta(1,$respuesta,200);
        }else{
            return $this->crearRespuesta(2,"No hay usuario que mostrar",200);
        }
    }
    public function obtenerUsuariosDeEntidad(Request $res)
    {
        $usuarios = "";
        $contar = 0;
        $take = $res["taken"];
        $pagina = $res["pagina"];
        $status = $res["status"];
        $palabra = $res["palabra"];
        $id_entidad = $res["id_entidad"];
        $tipo_entidad = $res["tipo_entidad"];
        $otro = "";
        if($status == "2"){
            $otro = "!=";
            $status = 2;
        }
        if($status == "1"){
            $status = 1;
            $otro = "=";
        }
        if($status == "0"){
            $status = 0;
            $otro = "=";
        }
        if($palabra == ""){
            $otro_dos = "!=";
            $palabra = "";
        }else{
            $otro_dos = "like";
            $palabra = "%".$palabra."%";
        }
        $incia = intval($pagina) * intval($take);
        $usuario_super_admin = DB::table('liga_usuario_sistema as lus')
        ->join("gen_cat_usuario as cu","lus.id_usuario","=","lus.id_usuario")
        ->where("id_sistema",5)
        ->first();
        if($tipo_entidad == 1){         //Es una entidad de tipo empresa
            $usuarios = DB::table('gen_cat_usuario as cu')
            ->join("liga_usuario_empresa as lue","lue.id_usuario","=","cu.id_usuario")
            ->where("cu.activo",$otro,$status)
            ->where("cu.nombre",$otro_dos,$palabra)
            ->where("cu.id_usuario","!=",$usuario_super_admin->id_usuario)
            ->where("lue.id_empresa",$id_entidad)
            ->skip($incia)
            ->take($take)
            ->get();
            $contar = DB::table('gen_cat_usuario as cu')
            ->join("liga_usuario_empresa as lue","lue.id_usuario","=","cu.id_usuario")
            ->where("cu.activo",$otro,$status)
            ->where("cu.usuario",$otro_dos,$palabra)
            ->where("cu.id_usuario","!=",$usuario_super_admin->id_usuario)
            ->where("lue.id_empresa",$id_entidad)
            ->get()
            ->count();
        }
        if($tipo_entidad == 2){
            $usuarios = DB::table('gen_cat_usuario as cu')
            ->join("liga_usuario_cliente as luc","luc.id_usuario","=","cu.id_usuario")
            ->where("cu.activo",$otro,$status)
            ->where("cu.nombre",$otro_dos,$palabra)
            ->where("cu.id_usuario","!=",$usuario_super_admin->id_usuario)
            ->where("luc.id_cliente",$id_entidad)
            ->skip($incia)
            ->take($take)
            ->get();
            $contar = DB::table('gen_cat_usuario as cu')
            ->join("liga_usuario_cliente as luc","luc.id_usuario","=","cu.id_usuario")
            ->where("cu.activo",$otro,$status)
            ->where("cu.usuario",$otro_dos,$palabra)
            ->where("cu.id_usuario","!=",$usuario_super_admin->id_usuario)
            ->where("luc.id_cliente",$id_entidad)
            ->get()
            ->count();
        }
        if(count($usuarios)>0){
            $respuesta = [
                "total" => $contar,
                "registros" => $usuarios
            ];
            return $this->crearRespuesta(1,$respuesta,200);
        }else{
            return $this->crearRespuesta(2,"No hay usuarios que mostrar",200);
        }
    }
    public function obtenerUsuarioPorId($id_usuario)
    {
        $validar = DB::table('cat_usuario as cu')
        ->select("cu.nombre","usuario","password","id_usuario","id_usuario as sistemas","cu.activo","cf.nombre as fotografia","cu.id_fotografia")
        ->join("gen_cat_fotografia as cf","cf.id_fotografia","=","cu.id_fotografia")
        ->where("cu.id_usuario",$id_usuario)
        ->get();
        if(count($validar)>0){
            $validar[0]->password = $this->decode_json($validar[0]->password);
            $validar[0]->fotografia = Storage::disk('usuario')->url($validar[0]->fotografia);
            $sistemas = DB::table('liga_usuario_sistema as lus')
            ->select("cs.sistema","cs.id_sistema")
            ->join("gen_cat_sistemas as cs","cs.id_sistema","=","lus.id_sistema")
            ->where("lus.id_usuario",$validar[0]->id_usuario)
            ->where("lus.activo",1)
            ->get();
            $validar[0]->sistemas = [];
            foreach($sistemas as $sistema){
                array_push($validar[0]->sistemas,["id_sistema" => $sistema->id_sistema,"sistema" => $sistema->sistema]);
            }
            return $this->crearRespuesta(1,$validar,200);
        }else{
            return $this->crearRespuesta(2,"No hay usuario que mostrar",200);
        }
    }
    public function obtenerSistemas(){
        return DB::table('gen_cat_sistemas')
        ->where("activo",1)
        ->where("id_sistema","!=",5)
        ->get();
    }
    public function obtenerSistemasAdmin($id_usuario)
    {
        $sistemas = DB::table('liga_usuario_sistema as lus')
        ->join("gen_cat_sistemas as cs","cs.id_sistema","=","lus.id_sistema")
        ->where("id_usuario",$id_usuario)
        ->where("lus.activo",1)
        ->get();
        if(count($sistemas)>0){
            return $this->crearRespuesta(1,$sistemas,200);
        }else{
            return $this->crearRespuesta(2,"No hay sistemas que mostrar",200);
        }
    }
    public function altaUsuario(Request $request)
    {
        //validate incoming request 
        $this->validate($request, [
            'nombre' => 'required|string|max:100',
            'usuario' => 'required|unique:gen_cat_usuario|max:50',
            'password' => 'required|max:50',
        ]);

        try {
            //Insertar fotografia
            $id_fotografia = $this->getSigId("gen_gen_cat_fotografia");
            $fecha = $this->getHoraFechaActual();
            $usuario_creacion = $request["usuario_creacion"];
            //Insertar fotografia
            if($request["fotografia"]["docB64"] == ""){
                //Guardar foto default
                DB::insert('insert into gen_gen_cat_fotografia (id_fotografia, nombre, fecha_creacion, usuario_creacion, activo) values (?,?,?,?,?)', [$id_fotografia,"usuario_default.svg",$fecha,$usuario_creacion,1]);
            }else{
                $file = base64_decode($request["fotografia"]["docB64"]);
                $nombre_image = "usuario_img_".$id_fotografia.".".$request["fotografia"]["extension"];
                DB::insert('insert into gen_cat_fotografia (id_fotografia, nombre, fecha_creacion, usuario_creacion, activo) values (?,?,?,?,?)', [$id_fotografia,$nombre_image,$fecha,$usuario_creacion,1]);
                Storage::disk('usuario')->put($nombre_image, $file);
            }
            //Nuevo usuario
            $user = new Usuario;
            $activo = $request->input('activo');
            $id_usuario = $this->getSigId("gen_cat_usuario");
            $user->id_usuario = $id_usuario; 
            $user->id_fotografia = $id_fotografia;
            $user->nombre = strtoupper($request->input('nombre'));
            $user->usuario = $request->input('usuario');
            $plainPassword = $request->input('password');
            $user->password = $this->encode_json($plainPassword);
            $user->fecha_creacion = $fecha;
            $user->usuario_creacion = $usuario_creacion;
            $user->activo = $activo;
            $user->save();

            $sistemas = $request->input("sistemas");
            foreach($sistemas as $sistema){
                $validar = $this->ligarUsuarioSistema($sistema,$id_usuario,$request->input("usuario_creacion"),$activo);    
            }
            //return successful response
            return $this->crearRespuesta(1,"Usuario registrado con éxito",200);

        } catch (\Throwable $th) {
            return $this->crearRespuesta(2,"Ha ocurrido un error: ".$th->getMessage(),301);
        }
    }
    public function altaUsuarioAdmin(Request $request)
    {
        //validate incoming request 
        $this->validate($request, [
            'nombre' => 'required|string|max:100',
            'usuario' => 'required|unique:gen_cat_usuario|max:50',
            'password' => 'required|max:50',
        ]);

        try {
            //Insertar fotografia
            $id_fotografia = $this->getSigId("gen_cat_fotografia");
            $fecha = $this->getHoraFechaActual();
            $usuario_creacion = $request["usuario_creacion"];
            //Insertar fotografia
            if($request["fotografia"]["docB64"] == ""){
                //Guardar foto default
                DB::insert('insert into gen_cat_fotografia (id_fotografia, nombre, fecha_creacion, usuario_creacion, activo) values (?,?,?,?,?)', [$id_fotografia,"usuario_default.svg",$fecha,$usuario_creacion,1]);
            }else{
                $file = base64_decode($request["fotografia"]["docB64"]);
                $nombre_image = "usuario_img_".$id_fotografia.".".$request["fotografia"]["extension"];
                DB::insert('insert into gen_cat_fotografia (id_fotografia, nombre, fecha_creacion, usuario_creacion, activo) values (?,?,?,?,?)', [$id_fotografia,$nombre_image,$fecha,$usuario_creacion,1]);
                Storage::disk('usuario')->put($nombre_image, $file);
            }
            $id_empresa = $request->input('id_empresa');
            $user = new Usuario;
            $activo = $request->input('activo');
            $id_usuario = $this->getSigId("gen_cat_usuario");
            $user->id_usuario = $id_usuario;
            $user->id_fotografia = $id_fotografia;
            $user->nombre = strtoupper($request->input('nombre'));
            $user->usuario = $request->input('usuario');
            $plainPassword = $request->input('password');
            $user->password = $this->encode_json($plainPassword);
            $user->fecha_creacion = $fecha;
            $user->usuario_creacion = $usuario_creacion;
            $user->activo = $activo;
            $user->save();

            $sistemas = $request->input("sistemas");
            foreach($sistemas as $sistema){
                $validar = $this->ligarUsuarioSistema($sistema,$id_usuario,$request->input("usuario_creacion"),$activo);    
            }
            //Dar de alta a usuario de empresa
            $id_usuario_empresa = $this->getSigId("liga_usuario_empresa");
            DB::insert('insert into liga_usuario_empresa (id_usuario_empresa, id_usuario, id_empresa, fecha_creacion, usuario_creacion, activo) values (?, ?, ?, ?, ?, ?)', [$id_usuario_empresa, $id_usuario, $id_empresa, $fecha, $usuario_creacion, 1]);
            
            return $this->crearRespuesta(1,"Usuario registrado con éxito",200);

        } catch (\Throwable $th) {
            return $this->crearRespuesta(2,"Ha ocurrido un error: ".$th->getMessage(),301);
        }
    }
    public function modificarUsuario(Request $request)
    {
        try {
            $fecha = $this->getHoraFechaActual();
            $usuario_modificacion = $request["usuario_creacion"];
            $id_fotografia = $request["fotografia"]["id_fotografia"];
            //Actualizar fotografia
            if($request["fotografia"]["docB64"] == ""){
                //Guardar foto default
                DB::update('update gen_gen_cat_fotografia set fecha_modificacion = ?, usuario_modificacion = ? where id_fotografia = ?', [$fecha,$usuario_modificacion,$id_fotografia]);
            }else{
                $file = base64_decode($request["fotografia"]["docB64"]);
                $nombre_image = "usuario_img_".$id_fotografia.".".$request["fotografia"]["extension"];
                if(Storage::disk('usuario')->has($nombre_image)){
                    Storage::disk('usuario')->delete($nombre_image);
                    DB::update('update gen_cat_fotografia set fecha_modificacion = ?, usuario_modificacion = ? where id_fotografia = ?', [$fecha,$usuario_modificacion,$request["fotografia"]["id_fotografia"]]);
                    Storage::disk('usuario')->put($nombre_image, $file);
                }else{
                    DB::update('update gen_cat_fotografia set nombre = ?, fecha_modificacion = ?, usuario_modificacion = ? where id_fotografia = ?', [$nombre_image,$fecha,$usuario_modificacion,$request["fotografia"]["id_fotografia"]]);
                    Storage::disk('usuario')->put($nombre_image, $file);
                }
            }
            $id_usuario = $request->input("id_usuario");
            $activo = $request->input('activo');
            $user = Usuario::find($id_usuario);
            $user->nombre = $request->input('nombre');
            $user->usuario = $request->input('usuario');
            $user->password = $this->encode_json($request->input("password"));
            $user->fecha_modificacion = $fecha;
            $user->usuario_modificacion = $usuario_modificacion;
            $user->activo = $activo;
            $user->save();
            
            $sistemas = $request->input("sistemas");
            //Resetear sistemas
            DB::update('update liga_usuario_sistema set activo = 0, fecha_modificacion = ?, usuario_modificacion = ? where id_usuario = ?', [$fecha,$request->input('usuario_creacion'),$id_usuario]);
            foreach($sistemas as $sistema){
                $validar = $this->ligarUsuarioSistema($sistema,$id_usuario,$request->input("usuario_creacion"),$activo);   //inserta los inexistentes 
                if(!$validar["ok"]){ ///Actualiza los existentes
                    DB::update('update liga_usuario_sistema set activo = ? where id_usuario_sistema = ?', [$activo, $validar["message"]]);
                }
            }
            //return successful response
            return $this->crearRespuesta(1,"Usuario modificado con éxito",200);

        } catch (\Throwable $th) {
            return $this->crearRespuesta(2,"Ha ocurrido un error: ".$th->getMessage(),301);
        }

    }
    public function ligarUsuarioSistema($id_sistema,$id_usuario,$usuario,$activo)
    {
        $fecha = $this->getHoraFechaActual();
        try{
            $id_liga = $this->getSigId("liga_usuario_sistema");
            $validar = DB::table('liga_usuario_sistema')
            ->where("id_sistema",$id_sistema)
            ->where("id_usuario",$id_usuario)
            ->get();
            if(count($validar)==0){
                DB::insert('insert into liga_usuario_sistema (id_usuario_sistema, id_usuario, id_sistema, fecha_creacion, usuario_creacion, activo) values (?,?,?,?,?,?)', [$id_liga,$id_usuario, $id_sistema, $fecha, $usuario, $activo]);
                return ["ok" => true];
            }else{
                return ["ok" => false,"message" => $validar[0]->id_usuario_sistema];
            }
        }catch (\Throwable $th) {
            return ["ok" => false,"message" => $th->getMessage()];
        }
    }
    public function login(Request $res)
    {
        $this->validate($res,[
            "usuario" => "string|required|max:50",
            "password" => "string|required|max:50"
        ]);
        $validar = $this->validarSesion($res["usuario"],$res["password"]);
        if (!$validar["ok"]) {
            return response()->json(['message' => $validar["message"]], 401);
        }
        $usuario = $validar["usuario"];
        if(intVal($usuario[0]->activo) == 0){
            return $this->crearRespuesta(2,"El usuario se encuentra desactivado",200);
        }
        $sistemas = DB::table("liga_usuario_sistema as lus")
        ->join("gen_cat_sistemas as gce","gce.id_sistema","=","lus.id_sistema")
        ->select("gce.id_sistema","gce.sistema")
        ->where("lus.id_usuario",$usuario[0]->id_usuario)
        ->where("lus.activo",1)
        ->get();
        $sistemas_info = [];
        foreach($sistemas as $sistema){ 
            array_push($sistemas_info,[
                "id" => $sistema->id_sistema,
                "sistema" => $sistema->sistema
            ]);
        }
        $respuesta = [
            "info_usuario" => [
                "id" => $usuario[0]->id_usuario,
                "nombre" => $usuario[0]->nombre,
                "url_foto" => $this->getEnv("APP_URL")."/api_serteza/resources/img/foto_perfil_".$usuario[0]->id_usuario.".png",
                "usuario" => $res["usuario"],
                "sistemas" => $sistemas_info
            ]
        ];
        return $this->crearRespuesta(1,$respuesta,200);
    }
    public function validarSesion($usuario,$password)
    {
        $validar = DB::table('gen_cat_usuario')
        ->where("usuario",$usuario)
        ->get();
        if(count($validar)>0){
            $password_decode = $this->decode_json($validar[0]->password);
            if($password_decode == $password){
                return ["ok"=> true,"usuario"=>$validar];
            }else{
                return ["ok"=> false,"message"=> "La contraseña ingresada no coincide con el usuario"];
            }
        }else{
            return ["ok"=> false,"message" => "El usuario ".$usuario." no existe o fue mal ingresado, intente de nuevo"];
        }
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
                $existeRfc = DB::table('con_provcliente')
                            ->select("id_provcliente")
                            ->where("rfc", $miData['rfcEmisor'])
                            ->count();
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
                if($existeRfc == 0){
                    DB::table('cat_direccion')->insert(
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
                    ]
                );
            }
        }
        return ["ok"=> true,"message"=> "xml`s insertador", "datos" => $data];
    }

}
