<?php

// SMTP Server
// http://www.elektronik-kompendium.de/sites/net/0903081.htm
class SMTP extends Server {
	protected $transports;

	public function __construct($bind_to, $transports = []) {
		$this->transports = $transports;
		parent::__construct($bind_to);
	}

	protected function onConnection(){
		$this->write('220 Hi');
	}

	protected function loop($data){
		// client data comes with line break
		$command = trim($data);

		if ($command === 'QUIT') {
			$this->write('221 Bye');
			fclose($this->connection);

			// process transports
			foreach ($this->transports as $transport) {
				call_user_func(array($transport, 'process'), $this->scoped_array['mail_data']);
			}
			unset($this->scoped_array['mail_data']);

			return true;
		} else if ($command === 'DATA') {
			$this->write('354 End data with .');
			$this->scoped_array['mail_data'] = '';
		} else if ($command === '.') {
			$this->write('250 Ok');
		} else if (isset($this->scoped_array['mail_data'])) {
			$this->scoped_array['mail_data'] .= $data;
		} else {
			$this->write('250 Ok');
		}
	}
}

// save mail into file
class SMTP_toFile {
	public function process($data) {
		if (!is_dir('mails')) mkdir('mails');
		file_put_contents('mails/' . uniqid(), $data);
	}
}

// forward to Gmail
class SMTP_toGmail {
	protected $config;
	protected $socket;

	public function __construct($config) {
		$this->config = $config;
	}

	public function process($mail_data) {
		echo "--- ON INIT GMAIL---\n";
		$this->socket = stream_socket_client($this->config['host'], $errno, $errstr);
		if (!$this->socket) die ("$errstr ($errno)");

		$this->read();
		$this->writeRead('EHLO gmail');
		$this->writeRead('AUTH LOGIN');
		$this->writeRead(base64_encode($this->config['user']));
		$this->writeRead(base64_encode($this->config['pass']));
		$this->writeRead("MAIL FROM: <{$this->config['user']}>");
		$this->writeRead("RCPT TO: <{$this->config['user']}>");
		$this->writeRead('DATA');
		$this->write($mail_data);
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
