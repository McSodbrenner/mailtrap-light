<?php

// DOES NOT WORK AT THE MOMENT

include('src/Server.php');
include('src/ImapServer.php');
new ImapServer('0.0.0.0:10143', __DIR__ . '/../mails.json');
