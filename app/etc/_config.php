<?php

$config = array(
    'system' => array(
        'path'          => '/var/www/demo.ecartcommerce.com/ecart',
        'applyUpgrades' => false
    ),

    'crypt' => array(
        'key' => '244c88521bda8173f524237a54c46827'
    ),

    'db' => array(
        'host'     => 'localhost',
        'username' => 'root',
        'password' => '123654',
        'dbname'   => 'axis',
        'prefix'   => ''
    ),

    'front' => array(
        'humanUrlAdapter' => "Readable"
    )
);
