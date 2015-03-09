<?php

// save mail into file
class ForwardToFile {
	public function process($from, $to, $data) {
		$dir = __DIR__ . '/../mails';
		if (!is_dir($dir)) mkdir($dir);
		file_put_contents($dir . '/' . uniqid(), $data);
	}
}
