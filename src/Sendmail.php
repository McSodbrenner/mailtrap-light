<?php

// Sendmail CLI Replacement
class Sendmail {
	protected $transports;
	protected $data = [];

	public function __construct($input, $transports) {
		$this->transports = $transports;

		preg_match_all('~^(From):.*?(\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b)~im', $input, $match);
		$this->data['from'] = $match[2];

		preg_match_all('~^(To|Cc|Bcc):.*?(\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b)~im', $input, $match);
		$this->data['to'] = $match[2];

		$this->data['data'] = $input;

		// process transports
		if (count($this->data) !== 0) {
			foreach ($this->transports as $transport) {
				call_user_func(array($transport, 'process'), $this->data['from'], $this->data['to'], $this->data['data']);
			}
		}
	}
}
