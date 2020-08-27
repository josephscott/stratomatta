<?php
// php examples/files.php start
use Stratomatta\Server;

require __DIR__ . '/../vendor/autoload.php';

$server = new Server( 'http://0.0.0.0:5000' );
$server->count = 4; // workers

$server->get( '/', __DIR__ . '/routes/index.php' );

$server->get( '/{name}[/{id:[0-9]+}]', __DIR__ . '/routes/name.php' );


$server->start();
