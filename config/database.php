<?php
return array(

    'default' => 'mysql',

    'connections' => array(

        # Our primary database connection
        'mysql' => array(
            'driver'    => env('DB_CONNECTION', 'mysql'),
            'host'      => env('DB_HOST', ''),
            'port'      => env('DB_PORT', ''),
            'database'  => env('DB_DATABASE', ''),
            'username'  => env('DB_USERNAME', ''),
            'password'  => env('DB_PASSWORD', ''),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ),

        # Our secondary database connection
    //     'mysql2' => array(
    //         'driver'    => env('DB_CONNECTION_RETYS', 'mysql'),
    //         'host'      => env('DB_HOST_RETYS', ''),
    //         'port'      => env('DB_PORT_RETYS', ''),
    //         'database'  => env('DB_DATABASE_RETYS', ''),
    //         'username'  => env('DB_USERNAME_RETYS', ''),
    //         'password'  => env('DB_PASSWORD_RETYS', ''),
    //         'charset'   => 'utf8',
    //         'collation' => 'utf8_unicode_ci',
    //         'prefix'    => '',
    //         'options'   => array(
    //             PDO::ATTR_EMULATE_PREPARES => true
    //          )
    //     ),
    ),
);