<?php

// save mail into file
class ForwardToFile {
	protected $datafile;

	public function __construct($datafile) {
		$this->datafile = $datafile;
		if (!is_file($this->datafile)) {
			touch($this->datafile);
		}
	}

	public function process($from, $to, $data) {
		$database = json_decode(file_get_contents($this->datafile), true);
		$database[] = [
			'from'	=> $from,
			'to'	=> $to,
			'flags'	=> '',
			'body'	=> $data,
		];
		file_put_contents($this->datafile, json_encode($database));
	}
}
