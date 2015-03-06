<?php

// SMTP Server
// http://www.elektronik-kompendium.de/sites/net/0903081.htm
class Smtp extends Server {
	protected $transports;
	protected $data = [];

	public function __construct($bind_to, $transports = []) {
		$this->transports = $transports;
		parent::__construct($bind_to);
	}

	protected function onConnection(){
		$this->write('220 Hi');
	}

	protected function loop($data){
		// client data comes with line break
		$command = substr($data, 0, 4);
		$params = array_map('trim', explode(' ', substr($data, 5)));


		// starts the smtp session
		if ($command === 'EHLO' || $command === 'HELO') {
			$this->write('250 Ok');

		// the first command that passes the sender fo the mail
		} else if ($command === 'MAIL') {
			preg_match('|<(.+)>|', $params[0], $match);
			$this->data['from'] = $match[1];
			$this->write('250 Ok');

		// can be executed more than once and contains the recipient
		} else if ($command === 'RCPT') {
			preg_match('|<(.+)>|', $params[0], $match);
			$this->data['to'][] = $match[1];
			$this->write('250 Ok');

		// DATA starts the transmission of the mail contents
		} else if ($command === 'DATA') {
			$this->write('354 End data with .');
			$this->data['data'] = '';
			$this->data['data_in_progress'] = true;

		// add mail data line for line
		} else if (isset($this->data['data_in_progress'])) {
			// the end of the DATA is marked with a dot
			if (trim($data) === '.') {
				$this->write('250 Ok');
				unset($this->data['data_in_progress']);
			} else {
				$this->data['data'] .= $data;
			}

		// cancels the current transmission but keeps the connection
		} else if ($command === 'RSET') {
			$this->data = [];
			$this->write('250 Ok');

		// Checks the recipient address
		} else if ($command === 'VRFY' || $command === 'EXPN') {
			$this->write('250 Ok');

		// forces an answer of the server to prevent a timeout
		} else if ($command === 'NOOP') {
			$this->write('250 Ok');

		// closes the connection to the client
		} else if ($command === 'QUIT') {
			$this->write('221 Bye');
			fclose($this->connection);

			// process transports
			foreach ($this->transports as $transport) {
				call_user_func(array($transport, 'process'), $this->data['from'], $this->data['to'], $this->data['data']);
			}

			$this->data = [];
			return true;

		// let's quit if we don't know what to do :)
		} else {
			$this->write('221 Bye');
			fclose($this->connection);
			$this->data = [];
			return true;
		}
	}
}
