<?php
//Rutas
$router->get('/', function () use ($router) {
    
});

$router->group(['prefix' => 'api'], function () use ($router) {
    //Rutas de Usuario
    $router->group(['prefix' => 'usuario'], function () use ($router) {
        $router->post('register', 'UsuarioController@register');       //Registro del usuario  
        $router->post('login', 'UsuarioController@login');       //Login del usuario
        $router->get('usuarios/{token}', [                       //Obtener usuarios 
            'middleware' => 'auth',
            'uses' => 'UsuarioController@obtenerUsuarios']
        );
    });

    $router->group(['prefix' => 'empresa'], function () use ($router) {
        $router->get('obtenerEmpresas/{sistema_id}/{token}',[    //Obtener empresas por id_usuario_sistema
            'middleware' => 'auth',
            'uses' => 'EmpresaController@obtenerEmpresa']
        ); 
    });

    $router->group(['prefix' => 'cliente'], function () use ($router) {         
        $router->get('obtenerClientes/{usuario_empresa_id}/{token}',[    //Obtener clientes por usuario_empresa_id
            'middleware' => 'auth',
            'uses' => 'ClienteController@obtenerClientes']
        ); 
    });
});
