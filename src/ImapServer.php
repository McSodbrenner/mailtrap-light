<?php

// IMAP Server
// http://www.phpgangsta.de/das-imap-protokoll-im-detail-betrachtet
class ImapServer extends Server {
	protected $to_delete = [];
	protected $database;
	protected $datafile;

	public function __construct($bind_to, $datafile) {
		$this->datafile = $datafile;
		$this->database = json_decode(file_get_contents($datafile), true);
		parent::__construct($bind_to);
	}

	protected function onConnection(){
		$this->write('* OK Hi');
	}

	protected function loop($data){
		// client data comes with line break
		preg_match('~(?P<identifier>\d+)\s+(?P<command>[A-Z]+)\s?(?P<params>.+)?~i', $data, $match);
		$identifier		= $match['identifier'];
		$command		= strtoupper($match['command']);
		$params			= isset($match['params']) ? $match['params'] : '';

		if ($command === 'CAPABILITY') {
			$this->write('* CAPABILITY IMAP4');
			$this->write($identifier . ' OK');
		} else if ($command === 'AUTHENTICATE') {
			$this->write($identifier . ' OK');
		} else if ($command === 'LOGIN') {
			$this->write($identifier . ' OK');
		} else if ($command === 'LOGOUT') {
			$this->write('* BYE');
			fclose($this->connection);

			// execute DELE actions
			/*
			foreach ($this->to_delete as $id) {
				$this->_dele($id);
			}
			file_put_contents($this->datafile, json_encode($this->database));
			*/

			return true;
		} else if ($command === 'SELECT') {
			$this->write('* FLAGS (\\Seen)');
			$this->write('* ' . count($this->database) . ' EXISTS');
			$this->write('* ' . count($this->database) . ' RECENT');
			$this->write($identifier . ' OK');
		} else if ($command === 'UID') {
			// parse fetch command
			if (preg_match('~^(?P<command>fetch)\s+(?P<min>\d+):?(?P<max>\d+|\*)?\s+\((?P<arguments>.*)\)~i', $params, $match)) {
				$match['max'] = ($match['max'] === '*') ? count($this->database) : $match['max'];
				$match['max'] = ($match['max'] === '') ? $match['min'] : $match['max'];

				// iterate all requested messages
				for ($i=$match['min']; $i<=$match['max']; $i++) {
					$mail = $this->database[$i-1];

					$temp = [];
					$arguments = explode(' ', $match['arguments']);
					$arguments[] = 'UID';
					foreach ($arguments as $arg) {
						//$arg = strtoupper($arg);

						if ($arg === 'FLAGS') {
							$temp[] = "{$arg} ({$mail['flags']})";
						} else if ($arg === 'UID') {
							$temp[] = "{$arg} {$i}";
						} else if ($arg === 'RFC822.SIZE') {
							$temp[] = "{$arg} ".strlen($mail['body'])."";
						} else if ($arg === 'RFC822.HEADER') {
							preg_match('|.+?\n\n|s', $mail['body'], $match2);
							$temp[] = "{$arg} {".strlen($match2[0])."}\r\n" . $match2[0];
						} else if ($arg === 'RFC822.peek') {
							$temp[] = "{$arg} {".strlen($mail['body'])."}\r\n" . $mail['body'];
						}
					}

					$temp = implode(' ', $temp);
					$answer = "* {$i} {$match['command']} ({$temp})";
					$this->write($answer);
				}
			}

			$this->write($identifier . ' OK');
		} else if ($command === 'NOOP') {
			$this->write($identifier . ' OK');
		} else {
			$this->write('-ERR');
		}
	}
}
