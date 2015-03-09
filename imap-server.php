<?php

include('src/Server.php');
include('src/ImapServer.php');
new ImapServer('0.0.0.0:10143');
