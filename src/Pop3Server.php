<?php

// POP3 Server
// http://www.elektronik-kompendium.de/sites/net/0903091.htm
class Pop3Server extends Server {
	protected $to_delete = [];

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
				$this->_dele($id);
			}

			return true;
		} else if ($command === 'NOOP') {
			$this->write('+OK');
		} else if ($command === 'STAT') {
			$this->write('+OK ' . $this->_stat());
		} else if ($command === 'LIST') {
			$this->write('+OK');
			foreach ($this->_list() as $item) {
				$this->write($item);
			}
			$this->write('.');
		} else if ($command === 'RETR') {
			$this->write('+OK');
			$this->write($this->_retr($params[0]));
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

	protected function _stat() {
		$filesize = 0;
		$files = glob('mails/*');
		foreach ($files as $file) {
			$filesize += filesize($file);
		}

		return count($files) . ' ' . $filesize;
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
