<?php

include('../src/Server.php');
include('../src/SmtpServer.php');
include('../src/ForwardToSmtp.php');
include('../src/ForwardToFile.php');

$forwarders = [
	new ForwardToFile(__DIR__ . '/mails.json'),
	/*
	new ForwardToSmtp([
		'host' => 'ssl://smtp.gmail.com:465',
		'user' => 'YOUR_USERNAME',
		'pass' => 'YOUR_PASSWORD',
	]),
	*/
];

new SmtpServer('0.0.0.0:10025', $forwarders);
