<?php
namespace Stratomatta;

use Workerman\Worker;
use Workerman\Protocols\Http\Response;
use FastRoute\Dispatcher;

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

	public function post( $path, $callback ) {
		$this->routes['POST'][] = [$path, $callback];
	}

	public function onMessage( $connection, $request ) {
		$match = $this->dispatcher->dispatch(
			$request->method(),
			$request->path()
		);

		if ( $match[0] === Dispatcher::FOUND ) {
			// $match[1] = handler
			// $match[2] = args

			if ( is_callable( $match[1] ) ) {
				$connection->send( $match[1]( $request, $match[2] ) );
				return true;
			}
		}

		if ( $match[0] === Dispatcher::NOT_FOUND ) {
			$connection->send( new Response( 404, [], 'Not Found' ) );
			return;
		}

		if ( $match[0] === Dispatcher::METHOD_NOT_ALLOWED ) {
			$connection->send( new Response( 405, [], 'Method Not Allowed' ) );
			return;
		}
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
