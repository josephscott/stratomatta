<?php
// php examples/callbacks.php start
use Stratomatta\Server;

require __DIR__ . '/../vendor/autoload.php';

$server = new Server( 'http://0.0.0.0:5000' );
$server->count = 4; // workers

$server->get( '/', function( $server ) {
	return 'The home';
} );

$server->get( '/{name}[/{id:[0-9]+}]', function( $server ) {
	return print_r( $server->args, true );
} );


$server->start();
