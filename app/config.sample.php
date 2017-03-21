<?php

// Rename this file to config.php

return [
    'db' => [
        'mysql:host=localhost;dbname=dbname',
        'root',
        ''
    ],
    // editor login and password
    'editor' => [
        'login' => 'admin',
        'password' => 'admin'
    ],
    // Issue cost
    'cost' => 15,

    // robokassa.ru payment service
    'robokassa' => [
        'login' => '',
        'password1' => '',
        'password2' => '',
        'test' => false // Is test mode
    ]
];
