<?php
// php examples/callbacks.php start
use Stratomatta\Server;

require __DIR__ . '/../vendor/autoload.php';

$server = new Server( 'http://0.0.0.0:5000' );
$server->count = 4; // workers

$server->get( '/', function( $request, $args ) {
	return 'The home';
} );

$server->get( '/{name}[/{id:[0-9]+}]', function( $request, $args ) {
	return print_r( $args, true );
} );


$server->start();
