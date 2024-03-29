<?php
declare( strict_types = 1 );

require __DIR__ . '/../vendor/autoload.php';

$server = new Stratomatta\Server( 'http://localhost:4747' );
$server->add_route( 'GET', '/', function () {
	$vars = get_defined_vars();

	$out = "<pre>\n";
	$out .= print_r( $vars, true );
	$out .= "\n---\n";
	$out .= print_r( $GLOBALS, true );
	$out .= "</pre>\n";

	return $out;
} );
$server->add_route( 'GET', '/page', __DIR__ . '/page.php' );
$server->start();
