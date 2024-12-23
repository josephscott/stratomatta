<?php
declare( strict_types = 1 );

beforeEach( function () {
	$this->http = new \JosephScott\Amulet();
} );

test( 'get', function () {
	$response = $this->http->get( url: 'http://127.0.0.1:31313/' );

	expect( $response->error )->toBe( false );
	expect( $response->code )->toBe( 200 );
} );
