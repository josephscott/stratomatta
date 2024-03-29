<?php
declare( strict_types = 1 );

namespace Stratomatta;

use FastRoute\Dispatcher;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;
use Workerman\Worker;

class Server extends Worker {
	protected $routes = [];
	protected $dispatcher = false;
	protected $route_cache_file = '';
	protected $route_cache_disabled = true;

	public function __construct(
		string $socket_name = 'http://localhost:7171',
		array $context_options = [],
		string $route_cache_file = '',
		bool $route_cache_disabled = true
	) {
		$this->route_cache_file = $route_cache_file;
		if ( empty( $route_cache_file ) ) {
			$this->route_cache_file = tempnam( '/tmp', 'stratomatta-cache-' );
		}
		$this->route_cache_disabled = $route_cache_disabled;

		parent::__construct( $socket_name, $context_options );
		$this->onMessage = [$this, 'onMessage'];
	}

	public function add_route(
		string $method,
		string $path,
		string|callable $callback
	):void {
		$this->routes[strtoupper( $method )][] = [ $path, $callback ];
	}

	public function do_route(
		string|callable $handler,
		TcpConnection $connection
	):void {
		if ( \is_callable( $handler ) ) {
			$connection->send( $handler() );
			return;
		}

		if ( is_readable( $handler ) ) {
			$call_file = function ( $handler ) {
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

	public function onMessage(
		TcpConnection $connection,
		Request $request
	):void {
		$match = $this->dispatcher->dispatch(
			$request->method(),
			$request->path()
		);

		if ( $match[0] === Dispatcher::FOUND ) {
			try {
				$this->do_route( $match[1], $connection );
				return;
			} catch ( \Throwable $e ) {
				error_log( var_export( $e, true ) );
				if ( isset( $this->routes['__500__'] ) ) {
					$this->do_route( $this->routes['__500__'], $connection );
				} else {
					$connection->send( new Response( 500, [], 'Fatal Error' ) );
				}
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

	public function start():void {
		$this->dispatcher = \FastRoute\cachedDispatcher(
			function ( \FastRoute\RouteCollector $r ) {
				foreach ( $this->routes as $method => $action ) {
					foreach ( $action as $route ) {
						// route[0] = path
						// route[1] = callback
						$r->addRoute( $method, $route[0], $route[1] );
					}
				}
			},
			[
				'cacheFile' => $this->route_cache_file,
				'cacheDisabled' => $this->route_cache_disabled,
			]
		);

		\Workerman\Worker::runAll();
	}
}
