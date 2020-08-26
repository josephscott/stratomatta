<?php
namespace Stratomatta;

use Workerman\Worker;
use Workerman\Protocols\Http\Response;
use FastRoute\Dispatcher;

class Server extends Worker {
	public function __construct(
		string $socket_name = '',
		array $context_options = []
	) {
		parent::__construct( $socket_name, $context_options );
	}

	public function start() {
		\Workerman\Worker::runAll();
	}
}
