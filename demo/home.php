<?php
declare( strict_types = 1 );

// $app is provided when the route handler is a plain PHP file

$app->response->Header( 'Content-Type', 'text-plain' );

echo "Hello, welcome to home.";
