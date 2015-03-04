<?php

include('server.php');
include('smtp.php');

$transports = [
	//new SMTP_toFile(),
	new SMTP_toGmail(include('../gmail.php')),
];

$smtp = new SMTP('172.20.0.206:10025', $transports);
