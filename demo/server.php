<?php
declare( strict_types = 1 );

require __DIR__ . '/../vendor/autoload.php';

$app = new \JosephScott\Stratomatta\App(
	origin: 'http://127.0.0.1:31313', // default
	routes_file: __DIR__ . '/routes.php'
);
$app->route_404 = __DIR__ . '/404.php';
//$app->session_name = 'THE_SESSION_ID_NAME';
$app->run();
