<?php
//Rutas
$router->get('/', function () use ($router) {
    return "RP_SERTEZA API";
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->get("obtenerCatalogo/{nombre_tabla}/{columnas}","Controller@obtenerCatalogo");
    $router->get("obtenerMovimientos/{id_empresa}","Controller@obtenerMovimientos");
    $router->post("obtenerCatalogoAutoComplete","Controller@obtenerCatalogoAutoComplete");
    $router->get("obtenerPerfiles","Controller@obtenerPerfiles");
    $router->get("decode_json/{code}","Controller@decode_json");
    $router->get("obtenerContratados/{id_puesto}","Controller@obtenerContratados");
    $router->get("obtenerMeses","Controller@obtenerMeses");
    
    //Rutas de Usuario
    $router->group(['prefix' => 'usuario'], function () use ($router) {
        $router->post('altaUsuario', 'UsuarioController@altaUsuario');                           //Registro del usuario 
        $router->post("autoCompleteUsuario","UsuarioController@autoComplete");
        $router->post('autoCompletePorIdEmpresa','UsuarioController@autoCompletePorIdEmpresa'); 
        $router->get("ligarUsuarioSistema/{id_sistema}/{id_usuario}/{usuario}","UsuarioController@ligarUsuarioSistema");            //Ligar usuario a sistema 
        $router->post('login', 'UsuarioController@login');                                       //Login del usuario
        $router->post('usuarios', 'UsuarioController@obtenerUsuarios');                           //Obtener todos los usuarios
        $router->get('obtenerSistemas', 'UsuarioController@obtenerSistemas');
        $router->get('obtenerSistemasPorIdUsuario/{id_usuario}', 'UsuarioController@obtenerSistemasPorIdUsuario');
        $router->get('obtenerSistemasAdmin/{id_usuario}', 'UsuarioController@obtenerSistemasAdmin');
        $router->get('obtenerUsuarioPorId/{id_usuario}', 'UsuarioController@obtenerUsuarioPorId');
        $router->get('obtenerUsuariosReclutamiento/{id_cliente}', 'UsuarioController@obtenerUsuariosReclutamiento');
        $router->get('obtenerUsuariosReclutamientoPorId/{id_usuario}', 'UsuarioController@obtenerUsuariosReclutamientoPorId');
        $router->post('obtenerUsuariosDeEntidad', 'UsuarioController@obtenerUsuariosDeEntidad');
        $router->post('modificarUsuario', 'UsuarioController@modificarUsuario');   
        $router->post('altaUsuarioAdmin', 'UsuarioController@altaUsuarioAdmin');
        $router->post('upload-xml', 'UsuarioController@xmlUpload');
        $router->post('tieneSistema', 'UsuarioController@tieneSistema');
        $router->get('activarDesactivarUsuario/{id_usuario}/{activo}', 'UsuarioController@activarDesactivarUsuario');
        $router->post("altaUsuarioSuperAdmin","UsuarioController@altaUsuarioSuperAdmin");
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
        $router->get("obtenerEmpresaPorRFC/{rfc}","EmpresaController@obtenerEmpresaPorIdCliente");
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
        $router->get('facObtenerClientes/{id_cliente}','ClienteController@facObtenerClientes');
        $router->get('facObtenerClientesPorId/{id_cliente}','ClienteController@facObtenerClientesPorId');
        $router->post('facAltaCliente','ClienteController@facAltaCliente');
        $router->get('facObtenerClientesPorRfc/{id_empresa}/{rfc}','ClienteController@facObtenerClientesPorRfc');
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
        $router->get('obtenerDepartamentoPorId/{id_departamento}',"DepartamentoController@obtenerDepartamentoPorId");
        $router->get('obtenerDepartamentosPorIdEmpresa/{id_empresa}',"DepartamentoController@obtenerDepartamentosPorIdEmpresa");
        $router->post('obtenerDepartamentosPorIdCliente',"DepartamentoController@obtenerDepartamentosPorIdCliente");
        $router->post('altaDepartamento',"DepartamentoController@altaDepartamento");
        $router->post('modificarDepartamento',"DepartamentoController@modificarDepartamento");
        $router->get('eliminarPuesto/{id_puesto}',"DepartamentoController@eliminarPuesto");
        $router->post('autoCompleteDepartamento',"DepartamentoController@autoComplete");
    });
    $router->group(['prefix' => "movimiento"], function () use ($router){
        $router->post('obtenerMovimientosReclutamiento','MovimientoController@obtenerMovimientosReclutamiento');
        $router->post('altaMovimiento','MovimientoController@altaMovimiento');
        $router->get("obtenerDetallePorId/{id_mov}","MovimientoController@obtenerDetallePorId");
        $router->get("obtenerDetalleBaja/{id_mov}","MovimientoController@obtenerDetalleBaja");
        $router->get("cancelarMovimiento/{id_mov}","MovimientoController@cancelarMovimiento");
        $router->post("modificarMovimiento","MovimientoController@modificarMovimiento");
        $router->post("modificarDetalle","MovimientoController@modificarDetalle");
        $router->get("cancelarDetalle/{id_detalle}","MovimientoController@cancelarDetalle");
        $router->get("cambiarStatusMov/{id_status}/{id_mov}","MovimientoController@cambiarStatusMov");
        $router->post("aplicarMovimiento","MovimientoController@aplicarMovimiento");
        $router->post("altaMovimientoPorExcel","MovimientoController@altaMovimientoPorExcel");
        $router->post("busquedaAltas","MovimientoController@busquedaAltas");
    });
    $router->group(['prefix' => "contratacion"], function () use ($router){
        $router->post('altaMovContratacion','ContratoController@altaMovContrato');
        $router->post('editarMovContrato','ContratoController@editarMovContrato');
        $router->post('obtenerMoviemientosContratacion','ContratoController@obtenerMoviemientosContratacion');
        $router->get('obtenerMoviemientosContratacionPorId/{id_movimiento}','ContratoController@obtenerMoviemientosContratacionPorId');
        $router->post('eliminarDetalleContratacion','ContratoController@eliminarDetalle');
        $router->get('obtenerCatalogoNomina','ContratoController@obtenerCatalogoNomina');
        $router->get('aplicarContratacion/{id_movimiento}/{usuario_creacion}','ContratoController@aplicarContratacion');
        $router->get('obtenerDocContratacion/{id_movimiento}/{id_contrato}','ContratoController@obtenerDocContratacion');
        $router->get('obtenerDocContratacionPorCandidato/{id_candidato}','ContratoController@obtenerDocContratacionPorCandidato');
    });
    $router->group(['prefix' => "contrato"], function () use ($router){
        $router->get("obtenerContratos/{id_empresa}","ContratoController@obtenerContratos");
        $router->post("altaContrato","ContratoController@altaContrato");
        $router->post("busquedaContrato","ContratoController@busquedaContrato");
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
        $router->get("obtenerEmpleadoPorIdCandidato/{id_candidato}","EmpleadoController@obtenerEmpleadoPorIdCandidato");
    });
    $router->group(['prefix' => 'reporte'], function () use ($router) {
        $router->get('reporteContratado/{id_detalle}','ReporteController@reporteContratado');
        $router->get('reporteModificacion/{id_detalle}','ReporteController@reporteModificacion');
        $router->get('reporteDepartamento/{id_empresa}/{id_cliente}','ReporteController@reporteDepartamento');
        $router->get('reporteContrato/{id_movimiento}','ReporteController@reporteContrato');
        $router->get('reporteEmpleado/{id_empleado}/{id_empresa}','ReporteController@reporteEmpleado');
        $router->post("reporteDepartamento","ReporteController@reporteDepartamento");
        $router->get('generarFactura/{id_factura}/{tipo}/{tipo_envio}','ReporteController@generarFactura');
    });
    $router->group(['prefix' => 'dashboard'], function () use ($router) {
        $router->get('obtenerDashboardAdmin/{id_empresa}','DashboardController@obtenerDashboardAdmin');
        $router->get('obtenerDashboardRh/{id_cliente}','DashboardController@obtenerDashboardRh');
        $router->post("obtenerDasboardFacturacion","DashboardController@obtenerDasboardFacturacion");
        $router->post("obtenerDatosEmpresaFacturacion","DashboardController@obtenerDatosEmpresaFacturacion");
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
        $router->post('eliminarLigaEmpresaNomina', 'NominaController@eliminarLigaEmpresaNomina');
        $router->get('activarLigaEmpresaNomina/{id_empresa_nomina}', 'NominaController@activarLigaEmpresaNomina');
        $router->post('altaSucursal', 'NominaController@altaSucursal');
        $router->post('aplicarSolicitudesRH', 'NominaController@aplicarSolicitudesRH');
        $router->get("buscarFolio/{folio}","NominaController@buscarFolio");
        $router->post("procesarCotizacion","NominaController@procesarCotizacion");
    });
    $router->group(['prefix' => 'concepto'], function () use ($router) { 
        $router->get('facObtenerConceptosEmpresa/{id_empresa}', 'ConceptoController@facObtenerConceptosEmpresa');
        $router->post("facServiciosAutocomplete","ConceptoController@facServiciosAutocomplete");
        $router->post("facUnidadesAutocomplete","ConceptoController@facUnidadesAutocomplete");
        $router->get('facObtenerConceptosPorId/{id_concepto}', 'ConceptoController@facObtenerConceptosPorId');
        $router->post("facAltaConcepto","ConceptoController@facAltaConcepto");
        $router->post('facModificarConcepto','ConceptoController@facModificarConcepto');
        $router->post('buscarConceptos','ConceptoController@buscarConceptos');        
        $router->get('buscarConceptosPorNombre/{id_empresa}/{concepto}', 'ConceptoController@buscarConceptosPorNombre');
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
        $router->post('obtenerPeriodosMensual', 'PeriodoController@obtenerPeriodosMensual');
    });
    $router->group(['prefix' => 'excel'], function () use ($router) {
        $router->get("formatoExcelCaptura/{empresa}","ExcelController@formatoCapturaConceptos");
        $router->get("formatoEmpleados/{empresa}/{id_nomina}","ExcelController@formatoEmpleados");
        $router->post("formatoAltaEmpleados","ExcelController@formatoAltaEmpleados");
    });
    $router->group(['prefix' => 'facturacion'], function () use ($router) {
        $router->post("obtenerFacturas","FacturacionController@obtenerFacturas");
        $router->post("altaFactura","FacturacionController@altaFactura");
        $router->post("opcionesFactura","FacturacionController@opcionesFactura");
        $router->post("descargaMasiva","FacturacionController@descargaMasiva");
        $router->post("generarExcel","FacturacionController@generarExcel");
        $router->post("facAltaFactura","FacturacionController@facAltaFactura");
        //Routes cataporte
        $router->post("facObtenerFacturas","FacturacionController@facObtenerFacturas");
        $router->post("facAltaOperador","FacturacionController@facAltaOperador");
        $router->post("facAltaPersona","FacturacionController@facAltaPersona");
        $router->get("facObtenerOperadores/{id_empresa}","FacturacionController@facObtenerOperadores");
        $router->post("facAltaVehiculo","FacturacionController@facAltaVehiculo");
        $router->get("facObtenerTransporte/{id_empresa}/{tipo}","FacturacionController@facObtenerTransporte");
        $router->get("facObtenerPersona/{id_empresa}","FacturacionController@facObtenerPersona");
        $router->post("facAltaUbicacion","FacturacionController@facAltaUbicacion");
        $router->post("facObtenerUbicacion","FacturacionController@facObtenerUbicacion");
        $router->get("getImportMercancias","FacturacionController@getImportMercancias");
        //Timbrado
        $router->post("timbrado","FacturacionController@timbrado");
        $router->post("getPDFPreview","FacturacionController@generaFacturaPreview");
        $router->get("cancelarTimbradoNomina/{foliofiscal}","FacturacionController@cancelarTimbradoNomina");
        //Descarga Masiva
        $router->post("descargaMasivaSAT","FacturacionController@descargaMasivaSAT");
        $router->post("crear-solicitud-sat","FacturacionController@crearSolicitudSat");
        $router->post("verificar-solicitud-sat","FacturacionController@verificarEstatusSat");
        $router->get("get-solicitudes-sat/{id_empresa}/{id_estatus}","FacturacionController@getSolicitudesSat");
        $router->post("descargar-solicitud-sat","FacturacionController@descargarDocumentosSat");
        $router->post("cancelar-solicitud-sat","FacturacionController@cancelarSolicitud");
        $router->post("altaBobedaXML", "FacturacionController@altaBobedaXML");
    });
    $router->group(['prefix' => 'serie'], function () use ($router) {
        $router->get("obtenerSeries/{id_empresa}","SerieController@obtenerSeries");
        $router->get("obtenerSeriePorId/{id_serie}","SerieController@obtenerSeriePorId");
        $router->post("altaSerie","SerieController@altaSerie");
        $router->post("modificarSerie","SerieController@modificarSerie");
        $router->get("facObtenerFolio/{id_serie}","SerieController@facObtenerFolioSig");
    });

    $router->group(['prefix' => 'gerencia'], function () use ($router) {
        $router->post('costoNomina', 'CostosController@costosNomina');
    });

    $router->group(['prefix' => 'contabilidad'], function () use ($router) {
        $router->post("ConceptosAutocomplete","conConceptoController@ConceptosAutocomplete");
        $router->get("ConceptosEmpresa/{id_empresa}","conConceptoController@ConceptosEmpresa");
        $router->get("ObtenerConceptoPorId/{id_concepto}","conConceptoController@ObtenerConceptoPorId");
        $router->post("AltaConcepto","conConceptoController@AltaConcepto");
        $router->post('ModificarConcepto','conConceptoController@ModificarConcepto');

        // Bancos
        $router->post("BancosAutocomplete","conBancoController@BancosAutocomplete");
        $router->get("BancosEmpresa/{id_empresa}","conBancoController@BancosEmpresa");
        $router->get("ObtenerBancoPorId/{id_catbanco}","conBancoController@ObtenerBancoPorId");
        $router->post("AltaBanco","conBancoController@AltaBanco");
        $router->post('ModificarBanco','conBancoController@ModificarBanco');

        // Movimientos
        $router->post("AltaMovBanco","conMovBancoController@AltaMovBanco");
        $router->post("ModificarMovBanco","conMovBancoController@ModificarMovBanco");
        $router->post("EstadoCuenta","conMovBancoController@EstadoCuenta");

    });
});
