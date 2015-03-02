<?php

$server = new Server;
$server->start('localhost', 10025,
	function() use ($server, &$scoped_array) {
		$server->write('220 Welcome to the Development SMTP Server (written in PHP).');
	},
	function($data) use ($server, &$scoped_array) {
		// http://www.elektronik-kompendium.de/sites/net/0903081.htm
		if ($data === 'QUIT') {
			$server->write('221 Bye');
			return true;
		} else if ($data === 'DATA') {
			$server->write('354 End data with .');
			$scoped_array['mail_data'] = '';
		} else if ($data === '.') {
			$server->write('250 Ok');
			file_put_contents('mails/' . uniqid() . '.txt', $scoped_array['mail_data']);
			unset($scoped_array['mail_data']);
		} else if (isset($scoped_array['mail_data'])) {
			$scoped_array['mail_data'] .= $data;
		} else {
			$server->write('250 Ok');
		}
	}
);

class Server {
	protected $msgsock;

	public function __construct() {
		error_reporting(E_ALL);

		// Allow the script to hang around waiting for connections.
		set_time_limit(0);

		// Turn on implicit output flushing so we see what we're getting as it comes in.
		ob_implicit_flush();
	}

	public function start($address, $port, $init_callback, $loop_callback) {
		if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) { echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n"; }
		socket_bind($sock, $address, $port) || die("socket_bind() failed: reason: " . socket_strerror(socket_last_error($sock)));
		socket_listen($sock, 5) !== false || die("socket_listen() failed: reason: " . socket_strerror(socket_last_error($sock)));

		do {
			if (($msgsock = socket_accept($sock)) === false) {
				echo "socket_accept() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
				break;
			}

			$scoped_array = [];

			$this->msgsock = $msgsock;
			echo "--- INIT ---\n";
			call_user_func($init_callback, $scoped_array);

			echo "--- LOOP ---\n";
			do {
				if (false === ($buf = socket_read($msgsock, 1024, PHP_NORMAL_READ))) {
					echo "socket_read() failed: reason: " . socket_strerror(socket_last_error($msgsock)) . "\n";
					break 2;
				}

				if (!$buf = trim($buf)) continue;

				echo "< $buf\n";
				if (call_user_func($loop_callback, $buf, $scoped_array)) break;

			} while (true);
			socket_close($msgsock);
		} while (true);

		socket_close($sock);
	}

	public function write($message) {
		echo "> $message\n";
		socket_write($this->msgsock, $message . "\r\n", strlen($message));
	}
}

