<?php
declare( strict_types = 1 );

require __DIR__ . '/../vendor/autoload.php';

$server = new Stratomatta\Server( 'http://localhost:4747' );
$server->add_route( 'GET', '/', __DIR__ . '/home.php' );
$server->start();
