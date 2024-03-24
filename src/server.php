<?php
declare( strict_types = 1 );

namespace Stratomatta;

use FastRoute\Dispatcher;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;
use Workerman\Worker;

class Server extends Worker {
	public function __construct(
		string $socket_name = 'http://localhost:7171',
		array $context_options = []
	) {
		parent::__construct( $socket_name, $context_options );
	}
}
