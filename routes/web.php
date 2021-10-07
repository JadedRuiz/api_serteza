<?php
//Rutas
$router->get('/', function () use ($router) {
    
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->get("obtenerCatalogo/{nombre_tabla}/{columnas}","Controller@obtenerCatalogo");
    $router->get("obtenerMovimientos/{id_empresa}","Controller@obtenerMovimientos");
    $router->post("obtenerCatalogoAutoComplete","Controller@obtenerCatalogoAutoComplete");
    $router->get("obtenerPerfiles","Controller@obtenerPerfiles");
    $router->get("decode_json/{code}","Controller@decode_json");
    
    //Rutas de Usuario
    $router->group(['prefix' => 'usuario'], function () use ($router) {
        $router->post('altaUsuario', 'UsuarioController@altaUsuario');                           //Registro del usuario 
        $router->post("autoCompleteUsuario","UsuarioController@autoComplete");
        $router->post('autoCompletePorIdEmpresa','UsuarioController@autoCompletePorIdEmpresa'); 
        $router->get("ligarUsuarioSistema/{id_sistema}/{id_usuario}/{usuario}","UsuarioController@ligarUsuarioSistema");            //Ligar usuario a sistema 
        $router->post('login', 'UsuarioController@login');                                       //Login del usuario
        $router->post('usuarios', 'UsuarioController@obtenerUsuarios');                           //Obtener todos los usuarios
        $router->get('obtenerSistemas', 'UsuarioController@obtenerSistemas');
        $router->get('obtenerSistemasAdmin/{id_usuario}', 'UsuarioController@obtenerSistemasAdmin');
        $router->get('obtenerUsuarioPorId/{id_usuario}', 'UsuarioController@obtenerUsuarioPorId');
        $router->post('obtenerUsuariosDeEntidad', 'UsuarioController@obtenerUsuariosDeEntidad');
        $router->post('modificarUsuario', 'UsuarioController@modificarUsuario');   
        $router->post('altaUsuarioAdmin', 'UsuarioController@altaUsuarioAdmin');
        $router->post('upload-xml', 'UsuarioController@xmlUpload');
        $router->post('tieneSistema', 'UsuarioController@tieneSistema');
    });
    $router->group(['prefix' => 'contabilidad'], function () use ($router) { 
        $router->post('upload-xml', 'ContabilidadController@xmlUpload');     
        $router->post('get-facturas-cp', 'ContabilidadController@getFacturasCP');     
        $router->post('guardar-factura', 'ContabilidadController@guardarFacturas');   
        $router->post('actualizar-factura', 'ContabilidadController@actualizarFacturas'); 
        $router->get('get-ivas/{id_empresa}', 'ContabilidadController@getCatIvas'); 
        $router->post('get-cliente-proveedor', 'ContabilidadController@buscarClienteProveedor');  
        $router->post('get-uuid', 'ContabilidadController@buscarUUID');
        
        $router->get('get-monedas', 'ContabilidadController@getMonedas'); 
        $router->get('get-metodos-pago', 'ContabilidadController@getMetodosPago'); 
        $router->get('get-tipos-comprobantes', 'ContabilidadController@getTipoComprobantes'); 
        $router->post('cancelar-factura', 'ContabilidadController@cancelarFactura'); 
        $router->get('get-conceptos/{id_empresa}', 'ContabilidadController@getConceptos'); 
    });
    $router->group(['prefix' => 'empresa'], function () use ($router) {
        $router->post('obtenerEmpresas','EmpresaController@obtenerEmpresas');         //Obtener empresas
        $router->post('autoCompleteEmpresa','EmpresaController@autoComplete');  
        $router->get('obtenerEmpresaPorId/{id}','EmpresaController@obtenerEmpresaPorId');        //Obtener empresas por Id
        $router->post('altaEmpresa','EmpresaController@altaEmpresa');
        $router->get("bajaEmpresa/{id}","EmpresaController@bajaEmpresa");
        $router->post("actualizarEmpresa","EmpresaController@actualizarEmpresa");
        $router->get("obtenerEmpresaPorIdUsuario/{id_usuario}","EmpresaController@obtenerEmpresaPorIdUsuario");
        $router->post("asignarEmpresaAUsuario","EmpresaController@asignarEmpresaAUsuario");
        $router->post("elimiminarLiga","EmpresaController@elimiminarLiga");
        $router->post("ligarClienteAEmpresa","EmpresaController@ligarClienteAEmpresa");
        $router->get("obtenerEmpresasPorIdCliente/{id_cliente}","EmpresaController@obtenerEmpresasPorIdCliente");
    });
    $router->group(['prefix' => 'cliente'], function () use ($router) {         
        $router->post('obtenerClientes','ClienteController@obtenerClientes');
        $router->get('obtenerClientesPorId/{id}','ClienteController@obtenerClientesPorId');
        $router->post("altaCliente","ClienteController@altaCliente");
        $router->post("actualizarCliente","ClienteController@actualizarCliente");
        $router->get("eliminarCliente/{id}","ClienteController@eliminarCliente");
        $router->get("obtenerClientePorIdUsuario/{id_usuario}","ClienteController@obtenerClientePorIdUsuario");
        $router->post("asignarClienteAUsuario","ClienteController@asignarClienteAUsuario");
        $router->post("elimiminarLiga","ClienteController@elimiminarLiga");
        $router->get("obtenerClientesPorIdEmpresa/{id_empresa}","ClienteController@obtenerClientesPorIdEmpresa");
        $router->post("autoCompleteCliente","ClienteController@autoComplete");
    });
    $router->group(['prefix' => 'candidato'], function () use ($router) {
        $router->get('obtenerDatos',"CandidatoController@obtenerDatosDashBoard");
        $router->post('altaCandidato','CandidatoController@altaCandidato');
        $router->post('obtenerCandidatos',"CandidatoController@obtenerCandidatos");
        $router->get('obtenerCandidatoPorId/{id}',"CandidatoController@obtenerCandidatoPorId");
        $router->get('obtenerCandidatosPorIdCliente/{id}',"CandidatoController@obtenerCandidatosPorIdCliente");
        $router->get("eliminarCandidato/{id}","CandidatoController@eliminarCandidato");
        $router->post("actualizarCandidato","CandidatoController@actualizarCandidato");
        $router->post('autoCompleteCandidato','CandidatoController@autoComplete');
        $router->get('obtenerCandidatoActivoId/{id_candidato}',"CandidatoController@obtenerCandidatoActivoId");
        $router->get("obtenerMovientosCandidato/{id_candidato}","CandidatoController@obtenerMovientosCandidato");
    });
    $router->group(['prefix' => 'puesto'], function () use ($router) {  
        $router->get("obtenerPuestosPorEmpresa/{id_empresa}","PuestoController@obtenerPuestosPorEmpresa");
        $router->get('eliminarPuesto/{id_puesto}',"PuestoController@eliminarPuesto");
        $router->get("obtenerPuestosPorIdDepartamento/{id_departamento}","PuestoController@obtenerPuestosPorIdDepartamento");
        
    });
    $router->group(['prefix' => 'departamento'], function () use ($router) {
        $router->post('obtenerDepartamentos',"DepartamentoController@obtenerDepartamentos");
        $router->get('obtenerDepartamentoPorId/{id_departamento}',"DepartamentoController@obtenerDepartamentoPorIdDepartamento");
        $router->post('altaDepartamento',"DepartamentoController@altaDepartamento");
        $router->post('actualizarDepartamento',"DepartamentoController@actualizarDepartamento");
        $router->post('autoCompleteDepartamento',"DepartamentoController@autoComplete");
    });
    $router->group(['prefix' => "contratacion"], function () use ($router){
        $router->post('altaMovContratacion','ContratoController@altaMovContrato');
        $router->post('editarMovContrato','ContratoController@editarMovContrato');
        $router->post('obtenerMoviemientosContratacion','ContratoController@obtenerMoviemientosContratacion');
        $router->get('obtenerMoviemientosContratacionPorId/{id_movimiento}','ContratoController@obtenerMoviemientosContratacionPorId');
        $router->post('eliminarDetalleContratacion','ContratoController@eliminarDetalle');
        $router->get('obtenerCatalogoNomina','ContratoController@obtenerCatalogoNomina');
        $router->get('aplicarContratacion/{id_movimiento}/{usuario_creacion}','ContratoController@aplicarContratacion');
    });
    $router->group(['prefix' => "modificacion"], function () use ($router){
        $router->post('solicitudDeModificacion','ModificacionController@crearSolicitudDeModif');
        $router->post('obtenerModificaciones','ModificacionController@obtenerModificaciones');
        $router->get('obtenerDetalleModificacion/{id_movimiento}','ModificacionController@obtenerDetalleModificacion');
        $router->post('modificarDetalleModificacion','ModificacionController@modificarDetalleModificacion');
        $router->get('eliminarDetalle/{id_detalle_modificacion}','ModificacionController@eliminarDetalle');
        $router->get('aplicarModificacion/{id_movimiento}',"ModificacionController@aplicarModificacion");
    });
    $router->group(['prefix' => "baja"], function () use ($router){
        $router->post('crearSolicitudDeBaja','BajaController@crearSolicitudDeBaja');
        $router->post('modificarDetalleSolicitud','BajaController@modificarDetalleSolicitud');
        $router->post('obtenerSolicitudesBaja','BajaController@obtenerSolicitudesBaja');
        $router->get('obtenerDetalleSolicitudBaja/{id_movimiento}','BajaController@obtenerDetalleSolicitudBaja');
        $router->get('eliminarDetalle/{id_detalle_baja}','BajaController@eliminarDetalle');
        $router->get('aplicarBaja/{id_movimiento}',"BajaController@aplicarBaja");
    });
    $router->group(['prefix' => 'empleado'], function () use ($router) {
        $router->post('autocompleteEmpleado','EmpleadoController@autocompleteEmpleado');
        $router->post("obtenerEmpleadosPorEmpresa","EmpleadoController@obtenerEmpleadosPorEmpresa");
        $router->post("obtenerCandidatoPorEmpresa","EmpleadoController@obtenerCandidatoPorEmpresa");
        $router->post("obtenerEmpleadoPorTipoNomina","EmpleadoController@obtenerEmpleadoPorTipoNomina");
        $router->get("obtenerEmpleadoPorId/{id_empleado}","EmpleadoController@obtenerEmpleadoPorId");
        $router->post('crearNuevoEmpleadoConCandidatoExistente','EmpleadoController@crearNuevoEmpleadoConCandidatoExistente');
        $router->post('crearNuevoEmpleado','EmpleadoController@crearNuevoEmpleado');
        $router->post("modificarEmpleadoAnt","EmpleadoController@modificarEmpleadoAnt");
        $router->post("cargaEmpleado","EmpleadoController@cargaEmpleado");
    });
    $router->group(['prefix' => 'reporte'], function () use ($router) {
        $router->get('reporteContratado/{id_detalle}','ReporteController@reporteContratado');
        $router->get('reporteContrato/{id_movimiento}','ReporteController@reporteContrato');
        $router->get('reporteEmpleado/{id_empleado}/{id_empresa}','ReporteController@reporteEmpleado');
    });
    $router->group(['prefix' => 'dashboard'], function () use ($router) {
        $router->get('obtenerDashboardAdmin/{id_empresa}','DashboardController@obtenerDashboardAdmin');
        $router->get('obtenerDashboardRh/{id_cliente}','DashboardController@obtenerDashboardRh');
    });
    $router->group(['prefix' => 'bancos'], function () use ($router) {
        $router->get('index', 'BancoController@index');
        $router->post('get-bancos', 'BancoController@busquedaBanco');
        $router->post('guardarbanco', 'BancoController@guardarBanco');
        $router->put('{id}', 'BancosController@actualizarBanco');
        $router->delete('{id}', 'BancosController@borrarBanco');
    });
    $router->group(['prefix' => 'nomina'], function () use ($router) {
        $router->get('obtenerNombreNominaPorId/{id_nomina}', 'NominaController@obtenerNombreNominaPorId');
        $router->post('insertarLigaNominaEmpresa', 'NominaController@insertarLigaNominaEmpresa');
        $router->post('obtenerLigaEmpresaNomina', 'NominaController@obtenerLigaEmpresaNomina');
        $router->get('eliminarLigaEmpresaNomina/{id_empresa_nomina}', 'NominaController@eliminarLigaEmpresaNomina');
        $router->get('activarLigaEmpresaNomina/{id_empresa_nomina}', 'NominaController@activarLigaEmpresaNomina');
        $router->post('altaSucursal', 'NominaController@altaSucursal');
        $router->post('aplicarSolicitudesRH', 'NominaController@aplicarSolicitudesRH');
    });
    $router->group(['prefix' => 'concepto'], function () use ($router) { 
        $router->post("autocomplete","ConceptoController@autocomplete");
        $router->get('obtenerConcpetosPorId/{id_empresa}', 'ConceptoController@obtenerConcpetosPorId');
        $router->get('obtenerConcpetosPorIdConcepto/{id_concepto}', 'ConceptoController@obtenerConcpetosPorIdConcepto');
        $router->post("crearConcepto","ConceptoController@crearConcepto");
        $router->post("modificarConcepto","ConceptoController@modificarConcepto");
        $router->get("cambiarActivo/{id_concepto}/{activo}","ConceptoController@cambiarActivo");
        //rutas para el consumo de captura de conceptos
        $router->get("obtenerConceptoPorIdMovNomina/{id_movnomina}","ConceptoController@obtenerConceptoPorIdMovNomina");
        $router->post("altaConceptosAEmpleado","ConceptoController@altaConceptoAEmpleado");
        $router->post("modificarConceptoAEmpleado","ConceptoController@modificarConceptoAEmpleado");
        $router->post("eliminarConceptoAEmpleado","ConceptoController@eliminarConceptoAEmpleado");
        $router->get("obtenerConceptosPorIdEmpleado/{id_empleado}/{id_empresa}/{id_periodo}","ConceptoController@obtenerConceptosPorIdEmpleado");
    });
    $router->group(['prefix' => 'sucursal'], function () use ($router) {
        $router->post('crearSucursal', 'SucursalController@crearSucursal');
        $router->get("obtenerSucursales/{id_empresa}","SucursalController@obtenerSucursales");
        $router->get("obtenerSucursalPorIdSucursal/{id_sucursal}","SucursalController@obtenerSucursalPorIdSucursal");
        $router->post('modificarSucursal', 'SucursalController@modificarSucursal');
    });
    $router->group(['prefix' => 'periodo'], function () use ($router) {
        $router->get("fechaFinalEjercicio/{anio}/{id_empresa}/{id_nomina}","PeriodoController@obtenerFechaFinalDelEjercicioAnt");
        $router->post('obtenerPeriodos', 'PeriodoController@obtenerPeriodos');
        $router->get("obtenerPeriodoPorId/{id_periodo}","PeriodoController@obtenerPeriodoPorId");
        $router->post('crearNuevoPeriodo', 'PeriodoController@crearNuevoPeriodo');
        $router->post('modificarPeriodo', 'PeriodoController@modificarPeriodo');
        $router->get("obtenerPeriodoEjercicioActual/{id_empresa}/{id_nomina}","PeriodoController@obtenerPeriodoEjercicioActual");
    });
    $router->group(['prefix' => 'excel'], function () use ($router) {
        $router->get("formatoExcelCaptura/{empresa}","ExcelController@formatoCapturaConceptos");
        $router->get("formatoEmpleados/{empresa}/{id_nomina}","ExcelController@formatoEmpleados");
    });
});
