<?php

// forward to Gmail
class SmtpToSmtp {
	protected $config;
	protected $socket;

	public function __construct($config) {
		$this->config = $config;
	}

	public function process($from, $to, $data) {
		echo "--- ON INIT GMAIL---\n";
		$this->socket = stream_socket_client($this->config['host'], $errno, $errstr);
		if (!$this->socket) die ("$errstr ($errno)");

		$this->read();
		$this->writeRead('EHLO Dude');
		$this->writeRead('AUTH LOGIN ' . base64_encode($this->config['user']));
		$this->writeRead(base64_encode($this->config['pass']));
		$this->writeRead("MAIL FROM: <{$this->config['user']}>");
		$this->writeRead("RCPT TO: <{$this->config['user']}>");
		$this->writeRead('DATA');

		// add a second dot to represent a single dot
		$data = preg_replace('|^\.$|m', '..', $data);

		$this->write($data);
		$this->writeRead('.');
		$this->write('QUIT');

		fclose($this->socket);
	}

	protected function read() {
		do {
			$server_response = fgets($this->socket);
			echo '< ' . $server_response;

			if (substr($server_response, 3, 1) == ' ') break;
		} while (true);
	}

	protected function write($message) {
		echo '> ' . $message . "\n";
		fwrite($this->socket, "{$message}\r\n");
	}

	protected function writeRead($message) {
		$this->write($message);
		$this->read();
	}
}
