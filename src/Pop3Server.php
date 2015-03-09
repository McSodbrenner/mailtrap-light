<?php

// POP3 Server
// http://www.elektronik-kompendium.de/sites/net/0903091.htm
class Pop3Server extends Server {
	protected $to_delete = [];
	protected $database;
	protected $datafile;

	public function __construct($bind_to, $datafile) {
		$this->datafile = $datafile;
		$this->database = json_decode(file_get_contents($datafile), true);
		parent::__construct($bind_to);
	}

	protected function onConnection(){
		$this->write('+OK Hi');
	}

	protected function loop($data){
		// client data comes with line break
		$command = substr($data, 0, 4);
		$params = array_map('trim', explode(' ', substr($data, 5)));

		if ($command === 'USER') {
			$this->write('+OK');
		} else if ($command === 'PASS') {
			$this->write('+OK');
		} else if ($command === 'QUIT') {
			$this->write('+OK');
			fclose($this->connection);

			// execute DELE actions
			foreach ($this->to_delete as $id) {
				unset($this->database[$id]);
			}
			file_put_contents($this->datafile, json_encode($this->database));

			return true;
		} else if ($command === 'NOOP') {
			$this->write('+OK');
		} else if ($command === 'STAT') {
			$size = 0;
			foreach ($this->database as $item) {
				$size += strlen($item['body']);
			}
			$this->write('+OK ' . count($this->database) . ' ' . $size);
		} else if ($command === 'LIST') {
			$this->write('+OK');
			foreach ($this->database as $key=>$item) {
				$this->write($key . ' ' . strlen($item['body']));
			}
			$this->write('.');
		} else if ($command === 'RETR') {
			$this->write('+OK');
			$this->write($this->database[$params[0]]['body']);
			$this->write('.');
		} else if ($command === 'DELE') {
			$this->to_delete[] = $params[0];
			$this->write('+OK');
		} else if ($command === 'RSET') {
			$this->to_delete = [];
			$this->write('+OK');
		} else if ($command === 'DATA') {
			$this->write('+OK');
			$this->scoped_array['mail_data'] = '';
		} else if ($command === '.') {
			$this->write('+OK');
			file_put_contents('mails/' . uniqid(), $this->scoped_array['mail_data']);
			unset($this->scoped_array['mail_data']);
		} else if (isset($this->scoped_array['mail_data'])) {
			$this->scoped_array['mail_data'] .= $data;
		} else {
			$this->write('-ERR');
		}
	}
}
