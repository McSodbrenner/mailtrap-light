<?php

include('../src/Server.php');
include('../src/Pop3Server.php');
new POP3Server('0.0.0.0:10110', __DIR__ . '/mails.json');
