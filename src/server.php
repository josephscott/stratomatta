<?php
namespace Stratomatta;

use Workerman\Worker;
use Workerman\Protocols\Http\Response;

class Server extends Worker {
	protected $routes = [];
	protected $dispatcher = false;

	public function __construct(
		string $socket_name = '',
		array $context_options = []
	) {
		parent::__construct( $socket_name, $context_options );
		$this->onMessage = [$this, 'onMessage'];
	}

	public function get( $path, $callback ) {
		$this->routes['GET'][] = [$path, $callback];
	}

	public function onMessage( $connection, $request ) {
	}

	public function start() {
		$this->dispatcher = \FastRoute\cachedDispatcher(
			function( \FastRoute\RouteCollector $r ) {
				foreach ( $this->routes as $method => $action ) {
					foreach ( $action as $route ) {
						// route[0] = path
						// route[1] = callback
						$r->addRoute( $method, $route[0], $route[1] );
					}
				}
			},
			[
				'cacheFile' => '/tmp/stratomatta-route.cache',
				'cacheDisabled' => true,
			]
		);

		\Workerman\Worker::runAll();
	}
}
