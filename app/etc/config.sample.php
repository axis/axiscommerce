<?php

$config = array(
    'system' => array(
        'path'    => '/var/www/htdocs/axiscommerce.com'
    ),
    
    'crypt' => array(
        'key' => 'crypt_key'
    ),
    
    'db' => array(
        'host'     => 'localhost',
        'username' => 'user_axis',
        'password' => 'password',
        'dbname'   => 'axis',
        'prefix'   => ''
    ),
    
    'front' => array(
        'humanUrlAdapter' => "Readable"
    )
);
