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

	public function __construct(
		string $socket_name = 'http://localhost:7171',
		array $context_options = []
	) {
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

	public function start():void {
		\Workerman\Worker::runAll();
	}
}
