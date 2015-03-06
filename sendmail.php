<?php

umask(0);
unlink('/tmp/sendmail');
$socket = stream_socket_server('unix:///tmp/sendmail', $errno, $errstr);
if (!$socket) die ("$errstr ($errno)");

$client = stream_socket_accept($socket);

while (true) {
	echo fgets($client);
}
//fclose($socket);
