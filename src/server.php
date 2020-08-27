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
			$handler = $match[1];
			$args = $match[2];

			if ( is_callable( $handler ) ) {
				$connection->send( $handler( $request, $args ) );
				return true;
			}

			if ( is_readable( $handler ) ) {
				$call_file = function( $handler ) use ( $request, $args ) {
					ob_start();
					require $handler;
					$out = ob_get_contents();
					ob_end_clean();
					return $out;
				};

				$connection->send( $call_file( $handler ) );
				return;
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
