#!/usr/bin/php
<?php

$path = ini_get('sendmail_path');
echo "$path\n";
var_dump(is_writable($path));
var_dump(mail('christoph.erdmann@ministry.de', 'subject', 'body'));

