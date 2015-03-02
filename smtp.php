<?php

// SMTP Server
// http://www.elektronik-kompendium.de/sites/net/0903081.htm
include('server.php');

class SMTP extends Server {
	protected function onConnection(){
		$this->write('220 Hi');
	}

	protected function loop($data){
		// client data comes with line break
		$command = trim($data);

		if ($command === 'QUIT') {
			$this->write('221 Bye');
			fclose($this->connection);
			return true;
		} else if ($command === 'DATA') {
			$this->write('354 End data with .');
			$this->scoped_array['mail_data'] = '';
		} else if ($command === '.') {
			$this->write('250 Ok');
			if (!is_dir('mails')) mkdir('mails');
			file_put_contents('mails/' . uniqid(), $this->scoped_array['mail_data']);
			unset($this->scoped_array['mail_data']);
		} else if (isset($this->scoped_array['mail_data'])) {
			$this->scoped_array['mail_data'] .= $data;
		} else {
			$this->write('250 Ok');
		}
	}
}

$smtp = new SMTP('172.20.0.206:10025');
