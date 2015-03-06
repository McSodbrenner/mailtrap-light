<?php

// save mail into file
class SmtpToFile {
	public function process($from, $to, $data) {
		if (!is_dir('mails')) mkdir('mails');
		file_put_contents('mails/' . uniqid(), $data);
	}
}
