<?php

// IMAP Server
// http://www.phpgangsta.de/das-imap-protokoll-im-detail-betrachtet
class ImapServer extends Server {
	protected $to_delete = [];

	protected function onConnection(){
		$this->write('* OK Hi');
	}

	protected function loop($data){
		// client data comes with line break
		preg_match('~(?P<identifier>\d+)\s+(?P<command>[A-Z]+)\s?(?P<params>.+)?~i', $data, $match);
		$identifier		= $match['identifier'];
		$command		= strtoupper($match['command']);
		$params			= isset($match['params']) ? explode(' ', $match['params']) : [];

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
			foreach ($this->to_delete as $id) {
				$this->_dele($id);
			}

			return true;
		} else if ($command === 'SELECT') {
			$this->write('* FLAGS (\Seen)');
			$this->write('* 259 EXISTS');
			$this->write('* 0 RECENT');
			$this->write($identifier . ' OK');
		} else if ($command === 'UID') {
			$mails = $this->_mails();
			$count = explode(':', $params[1]);

			foreach ($mails as $i=>$mail) {
				$this->write("* ".($i+1)." FETCH (FLAGS ())");
			}

			$this->write($identifier . ' OK');
		} else if ($command === 'NOOP') {
			$this->write($identifier . ' OK');
		} else {
			$this->write('-ERR');
		}
	}

	protected function _mails() {
		$filesize = 0;
		$files = glob('mails/*');
		return $files;
	}

	protected function _list() {
		$items = [];
		$files = glob('mails/*');
		foreach ($files as $i => $file) {
			$items[] = "{$i} " . filesize($file);
		}

		return $items;
	}

	protected function _retr($id) {
		$files = glob('mails/*');
		return file_get_contents($files[$id]);
	}

	protected function _dele($id) {
		$files = glob('mails/*');
		if (isset($files[$id])) {
			unlink($files[$id]);
		}
	}

}
