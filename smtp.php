<?php

// SMTP Server
// http://www.elektronik-kompendium.de/sites/net/0903081.htm
include('server.php');

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

	protected function write($message) {
		echo "> {$message}\n";
		fwrite($this->connection, "{$message}\r\n");
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

	public function process($data) {
		echo "--- ON INIT GMAIL---\n";
		$this->socket = stream_socket_client($this->config['host'], $errno, $errstr);

		$this->read();

		$this->write('EHLO gmail');
		$this->read();

		$this->write('AUTH LOGIN');
		$this->read();

		$this->write(base64_encode($this->config['user']));
		$this->read();

		$this->write(base64_encode($this->config['pass']));
		$this->read();

		$this->write("MAIL FROM: <{$this->config['user']}>");
		$this->read();

		$this->write("RCPT TO: <{$this->config['user']}>");
		$this->read();

		$this->write('DATA');
		$this->read();

		$this->write($data);
		$this->write('.');
		$this->read();

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
		fwrite($this->socket, $message . "\r\n");

	}
}

$mail =
$transports = [
	//new SMTP_toFile(),
	new SMTP_toGmail(include('../gmail.php')),
];

$smtp = new SMTP('172.20.0.206:10025', $transports);
