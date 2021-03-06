<?php
namespace Stratomatta;

use Workerman\Worker;
use Workerman\Protocols\Http\Response;
use FastRoute\Dispatcher;

class Server extends Worker {
	protected $routes = [];
	protected $dispatcher = false;
	protected $container = [];

	public function __construct(
		string $socket_name = '',
		array $context_options = []
	) {
		parent::__construct( $socket_name, $context_options );
		$this->onMessage = [$this, 'onMessage'];
	}

	public function container_add( $thing ) {
		$this->container[] = $thing;
	}

	public function container_get( $thing ) {
		return $this->container[$thing];
	}

	public function any( $path, $callback ) {
		$this->get( $path, $callback );
		$this->post( $path, $callback );
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
			try {
				$handler = $match[1];

				$server = new \StdClass();
				$server->request = $request;
				$server->args = $match[2];
				$server->container = $this->container;

				if ( is_callable( $handler ) ) {
					$connection->send( $handler( $server ) );
					return true;
				}

				if ( is_readable( $handler ) ) {
					$call_file = function( $handler ) use ( $server ) {
						ob_start();
						require $handler;
						$out = ob_get_contents();
						ob_end_clean();
						return $out;
					};

					$connection->send( $call_file( $handler ) );
					return;
				}
			} catch ( \Throwable $e ) {
				error_log( print_r( $e, true ) );
				$connection->send( new Response( 500, [], 'Fatal Error' ) );
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
