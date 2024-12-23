<?php
declare( strict_types = 1 );

namespace JosephScott\Stratomatta;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;
use Workerman\Protocols\Http\Session as SessionBase;
use Workerman\Worker;

class App {
	public mixed $route_404 = null;

	public string $server_name = '';

	public mixed $session;

	public int $worker_count = 10;

	private string $origin = 'http://127.0.0.1:31313';

	private object $router;

	private string $routes_file;

	private object $worker;

	public function __construct( string $origin, string $routes_file ) {
		$this->origin = $origin;
		$this->routes_file = $routes_file;
		$this->session = SessionBase::class;

		if ( ! is_readable( $routes_file ) ) {
			$msg = "Stratomatta: Routes file not found or not readable: $routes_file";
			error_log( $msg );
			echo $msg . "\n";
			exit( 1 );
		}
	}

	public function call_route(
		string|array|callable $handler,
		array $vars,
		Request $request,
		Response $response
	) : Response {
		$app = new \stdClass();
		$app->handler = $handler;
		$app->request = $request;
		$app->response = $response;
		$app->session = $this->session;
		$app->vars = $vars;

		$out = '';

		if ( is_string( $handler ) && is_readable( $handler ) ) {
			$call_file = function ( $app ) {
				ob_start();
				require $app->handler;
				$out = ob_get_contents();
				ob_end_clean();
				return $out;
			};
			$out = $call_file( $app );
		}elseif ( is_callable( $handler ) ) {
			$out = $handler( $app );
		}

		$app->response->withBody( $out );
		return $app->response;
	}

	public function load_routes(): void {
		$this->router = simpleDispatcher( function ( RouteCollector $router ) {
			require $this->routes_file;
		} );
	}

	public function on_message( TcpConnection $connection, Request $request ) : void {
		$response = new Response( 200, [] );
		$response->withHeader( 'Server', $this->server_name );

		$match = $this->router->dispatch( $request->method(), $request->path() );
		switch ( $match[0] ) {
			case Dispatcher::FOUND:
				$handler = $match[1];
				$vars = $match[2];
				$response->withStatus( 200 );
				$response = $this->call_route( $handler, $vars, $request, $response );
				break;
			case Dispatcher::NOT_FOUND:
				// If there was no trailing slash, redirect to the same URL with a trailing slash
				if ( $request->path() !== '/' && substr( $request->path(), -1 ) !== '/' ) {
					$response->withStatus( 301 );
					$response->withHeader( 'Location', $request->path() . '/' );
				} else {
					if ( $this->route_404 !== null ) {
						$response = $this->call_route( $this->route_404, [], $request, $response );
					} else {
						$response->withBody( '404 Not Found' );
					}
					$response->withStatus( 404 );
				}
				break;
			case Dispatcher::METHOD_NOT_ALLOWED:
				$response->withStatus( 405 );
				break;
		}

		$connection->send( $response );
	}

	public function on_worker_start( Worker $worker ) : void {
		$this->load_routes();
	}

	public function run() : void {
		$this->worker = new Worker( $this->origin );
		$this->worker->count = $this->worker_count;
		$this->worker->onWorkerStart = [ $this, 'on_worker_start' ];
		$this->worker->onMessage = [ $this, 'on_message' ];
		$this->worker->runAll();
	}
}
