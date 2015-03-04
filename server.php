<?php

class Server {
	protected $connection;
	protected $scoped_array;

	public function __construct($bind_to) {
		error_reporting(E_ALL);
		set_time_limit(0);
		ob_implicit_flush();

		echo "--- ON INIT ---\n";
		$socket = stream_socket_server('tcp://' . $bind_to, $errno, $errstr);
		stream_set_timeout($socket, 3);
		if (!$socket) die ("$errstr ($errno)");
		echo 'Server "' . get_class($this) . '" initialized at ' . stream_socket_get_name($socket, false) . "\n";

		do {
			$this->connection = @stream_socket_accept($socket); // @ to suppress timeout warning
			if ($this->connection === false) continue;

			echo "--- ON CONNECTION ---\n";
			$this->onConnection();

			echo "--- LOOP ---\n";
			do {

				$buf = fgets($this->connection);
				if (strlen($buf) === 0) continue;

				if (DEBUG) echo "< {$buf}";
				if ($this->loop($buf)) break;
			} while (true);
		} while (true);

		fclose($socket);
	}

	protected function write($message) {
		echo "> {$message}\n";
		fwrite($this->connection, "{$message}\r\n");
	}

	protected function onConnection() {}
	protected function loop() {}
}

