<?php

// Sendmail CLI Replacement
class Sendmail {
	public function __construct($input) {
		$data = imap_rfc822_parse_headers($input);
		print_r($input);

		preg_match('~^(To|Cc|Bcc):.*<(.+)>.*$~im', $input, $match);
		print_r($match);
	}
}
