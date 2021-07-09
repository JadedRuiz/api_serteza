<?php

namespace App\Http\Controllers;
use App\Models\CatBanco;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class BancoController extends Controller
{
    public function busquedaBanco(Request $request){
        $take = $request["taken"];
        $pagina = $request["pagina"];
        $empresaID = $request["id_empresa"];
        $bancoID = $request["id_catbanco"];
        $incia = intval($pagina) * intval($take);

        if($bancoID > 0){
            $query = DB::table('ban_catbancos as b')
            ->join("sat_catbancos AS s","s.id_bancosat", "=", "b.id_bancosat")
            ->join("gen_cat_empresa AS e","e.id_empresa", "=", "b.id_empresa")
            ->select(DB::raw("b.*"), "s.c_banco, e.empresa, s.descripcion", 
            DB::raw("CONCAT(b.cuenta,'/',s.descripcion) AS banco"))
            ->where("b.id", $bancoID)
            ->orderBy("s.descripcion", "ASC")
            ->orderBy("b.cuenta", "ASC")
            ->skip($incia)
            ->take($take)
            ->get();

         }else{
            $query = DB::table('ban_catbancos as b')
            ->join("sat_catbancos AS s","s.id_bancosat", "=", "b.id_bancosat")
            ->join("gen_cat_empresa AS e","e.id_empresa", "=", "b.id_empresa")
            ->select(DB::raw("b.*,s.c_banco, e.empresa, s.descripcion"),
            DB::raw("CONCAT(b.cuenta,'/',s.descripcion) AS banco"))
            ->where("b.id_empresa", $empresaID)
            ->orderBy("s.descripcion", "ASC")
            ->orderBy("b.cuenta", "ASC")
            ->skip($incia)
            ->take($take)
            ->get();
         }

        
        
        return $this->crearRespuesta(1,$query, 200);

    }
    public function index(){
        return 'desdeController';
    }

    public function guardarBanco(Request $request) {
        $usuarioID = $request->get("id_catusuarios_c");
        
            CatBanco::create($request->all());
            $ultimo=DB::getPdo()->lastInsertId();


            //CatPermiso::create(0,$ultimo,$usuarioID);

            return $this->crearRespuesta(1,'El elemento ha sido creado', 201);
        
    }
    //
    public function actualizarBanco(Request $request, $id) {
        $CatBanco = CatBanco::find($id);
        if  (!is_null($CatBanco)){
            
                $cuenta = $request->get('cuenta');
                $tarjeta = $request->get('tarjeta');
                $clabe = $request->get('clabe');
                $id_bancosat = $request->get('id_bancosat');
                $cuentacontable = $request->get('cuentacontable');
                $contrato = $request->get('contrato');
                $id_catempresas = $request->get('id_empresa');
                $usuario_modificacion = $request->get('usuario_modificacion');
                
                $CatBanco->cuenta = $cuenta;
                $CatBanco->tarjeta = $tarjeta;
                $CatBanco->clabe = $clabe;
                $CatBanco->cuentacontable = $cuentacontable;
                $CatBanco->contrato = $contrato; 
                $CatBanco->id_empresa = $id_catempresas;          
                $CatBanco->usuario_modificacion = $usuario_modificacion;
                
                $CatBanco->save();

                return $this->crearRespuesta(1,'El elemento ha sido modificado', 201);
        }
        return $this->crearRespuestaError(2,'No existe el id', 300);
    }

    public function borrarBanco($id) {
        $CatBanco = CatBanco::find($id);
        
        if ($CatBanco){
            
            $CatBanco->delete();
            return $this->crearRespuesta(1,'El elemento ha sido eliminado', 201);
        }
        return $this->crearRespuestaError(2,'No existe el id', 300);
    }
}