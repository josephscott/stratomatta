<?php
declare( strict_types = 1 );

require __DIR__ . '/../vendor/autoload.php';

$app = new \JosephScott\Stratomatta\App(
	origin: 'http://127.0.0.1:31313', // default
	routes_file: __DIR__ . '/routes.php'
);
$app->run();
