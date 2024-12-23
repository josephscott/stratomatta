<?php
declare( strict_types = 1 );

test( 'get', function () {
	$http = new \JosephScott\Amulet();
	$response = $http->get( url: 'http://127.0.0.1:31313/' );

	expect( $response->error )->toBe( false );
	expect( $response->code )->toBe( 200 );
} );
