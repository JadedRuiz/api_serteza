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
});
