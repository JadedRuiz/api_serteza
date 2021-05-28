<?php
//Rutas
$router->get('/', function () use ($router) {
    
});

$router->group(['prefix' => 'api'], function () use ($router) {
    //Rutas de Usuario
    $router->group(['prefix' => 'usuario'], function () use ($router) {
        $router->post('altaUsuario', 'UsuarioController@altaUsuario');                           //Registro del usuario 
        $router->get("ligarUsuarioSistema/{id_sistema}/{id_usuario}/{usuario}","UsuarioController@ligarUsuarioSistema");            //Ligar usuario a sistema 
        $router->post('login', 'UsuarioController@login');                                       //Login del usuario
        $router->post('usuarios', 'UsuarioController@obtenerUsuarios');                           //Obtener todos los usuarios
        $router->get('obtenerSistemas', 'UsuarioController@obtenerSistemas');
        $router->get('obtenerSistemasAdmin/{id_usuario}', 'UsuarioController@obtenerSistemasAdmin');
        $router->get('obtenerUsuarioPorId/{id_usuario}', 'UsuarioController@obtenerUsuarioPorId');
        $router->post('obtenerUsuariosDeEntidad', 'UsuarioController@obtenerUsuariosDeEntidad');
        $router->post('modificarUsuario', 'UsuarioController@modificarUsuario');   
        $router->post('altaUsuarioAdmin', 'UsuarioController@altaUsuarioAdmin');       
    });

    $router->group(['prefix' => 'empresa'], function () use ($router) {
        $router->post('obtenerEmpresas','EmpresaController@obtenerEmpresas');         //Obtener empresas
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
    });
    
    $router->group(['prefix' => 'candidato'], function () use ($router) {
        $router->get('obtenerDatos',"CandidatoController@obtenerDatosDashBoard");
        $router->post('altaCandidato','CandidatoController@altaCandidato');
        $router->post('obtenerCandidatos',"CandidatoController@obtenerCandidatos");
        $router->get('obtenerCandidatoPorId/{id}',"CandidatoController@obtenerCandidatoPorId");
        $router->get('obtenerCandidatosPorIdCliente/{id}',"CandidatoController@obtenerCandidatosPorIdCliente");
        $router->get("eliminarCandidato/{id}","CandidatoController@eliminarCandidato");
        $router->post("actualizarCandidato","CandidatoController@actualizarCandidato");
    });

    $router->group(['prefix' => 'puesto'], function () use ($router) {  
        $router->get("getPuestosPorIdEmpresa/{id_empresa}","PuestoController@getPuestosPorIdEmpresa");
        $router->get('eliminarPuesto/{id_puesto}',"PuestoController@eliminarPuesto");
        $router->get("obtenerPuestosPorIdDepartamento/{id_departamento}","PuestoController@obtenerPuestosPorIdDepartamento");
    });

    $router->group(['prefix' => 'departamento'], function () use ($router) {
        $router->post('obtenerDepartamentos',"DepartamentoController@obtenerDepartamentos");
        $router->get('obtenerDepartamentoPorId/{id_departamento}',"DepartamentoController@obtenerDepartamentoPorIdDepartamento");
        $router->post('altaDepartamento',"DepartamentoController@altaDepartamento");
        $router->post('actualizarDepartamento',"DepartamentoController@actualizarDepartamento");
    });
    $router->group(['prefix' => "contratacion"], function () use ($router){
        $router->post('altaMovContratacion','ContratoController@altaMovContrato');
    });
    $router->group(['prefix' => 'no_recuerdo'], function () use ($router) {
    });
});
