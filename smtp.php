<?php

include('src/Server.php');
include('src/Smtp.php');
include('src/SmtpToSmtp.php');
include('src/SmtpToFile.php');

$transports = [
	/*
	new SmtpToFile(),
	new SmtpToSmtp([
		'host' => 'ssl://smtp.gmail.com:465',
		'user' => 'YOUR_USERNAME',
		'pass' => 'YOUR_PASSWORD',
	]),
	*/

	// I do this so you don't have my mail credentials at the github repository :P
	new SmtpToSmtp(include('../gmail.php')),
];

new Smtp('0.0.0.0:10025', $transports);
